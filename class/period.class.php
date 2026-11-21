<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 */

/** Calendar-day rules ported from PR #42620; no business writes. */
class MsmPeriodService
{
	/**
	 * Shift calendar components in UTC, then return midnight in the form's timezone.
	 * @param int $date Date in the same timezone convention as GETPOSTDATE/selectDate
	 * @param int $quantity Calendar duration
	 * @param string $unit Duration unit
	 * @return int Shifted date
	 */
	public static function subscriptionCalendarShift($date, $quantity, $unit)
	{
		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
		$calendar = dol_mktime(0, 0, 0, (int) dol_print_date($date, '%m'), (int) dol_print_date($date, '%d'), (int) dol_print_date($date, '%Y'), 'gmt');
		// V22 dol_time_plus_duree has no timezone argument. Use UTC calendar
		// arithmetic locally, retaining its DateInterval overflow semantics.
		if (!in_array($unit, array('d', 'w', 'm', 'y'), true) || abs($quantity) > 3000000) {
			throw new RuntimeException('SubscriptionBulkInvalidDate');
		}
		$utc = (new DateTimeImmutable('@'.$calendar))->setTimezone(new DateTimeZone('UTC'));
		$interval = new DateInterval('P'.abs($quantity).strtoupper($unit));
		$shifted = ($quantity < 0 ? $utc->sub($interval) : $utc->add($interval))->getTimestamp();
		if (!$shifted || (int) dol_print_date($shifted, '%Y', 'gmt') < 1970 || (int) dol_print_date($shifted, '%Y', 'gmt') > 9998) {
			throw new RuntimeException('SubscriptionBulkInvalidDate');
		}
		return dol_mktime(0, 0, 0, (int) dol_print_date($shifted, '%m', 'gmt'), (int) dol_print_date($shifted, '%d', 'gmt'), (int) dol_print_date($shifted, '%Y', 'gmt'));
	}

	/**
	 * Explicit calendar alignment; month takes precedence as in subscription.php.
	 * @param int $start Individual start date
	 * @return int|null Calendar end, null for invalid start or no configured rule
	 */
	public static function subscriptionCalendarEndForBatch($start)
	{
		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
		if ($start <= 0 || (!getDolGlobalInt('MEMBER_SUBSCRIPTION_SUGGEST_END_OF_MONTH') && !getDolGlobalInt('MEMBER_SUBSCRIPTION_SUGGEST_END_OF_YEAR'))) {
			return null;
		}
		$year = (int) dol_print_date($start, '%Y');
		$month = getDolGlobalInt('MEMBER_SUBSCRIPTION_SUGGEST_END_OF_MONTH') ? (int) dol_print_date($start, '%m') : 12;
		$last = dol_get_last_day($year, $month, 'gmt');
		return dol_mktime(0, 0, 0, $month, (int) dol_print_date($last, '%d', 'gmt'), $year);
	}

	/**
	 * Use the member type duration without truncating it to calendar suggestions.
	 * @param int $date Start date
	 * @param AdherentType $type Member type
	 * @return int End date
	 */
	public static function subscriptionEndDateForBatch($date, $type)
	{
		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
		if ($date <= 0) {
			throw new RuntimeException('SubscriptionBulkInvalidDate');
		}
		$duration = self::duration($type);
		$delay = $duration[0];
		$unit = $duration[1];
		if ($delay <= 0 || !in_array($unit, array('d', 'w', 'm', 'y'), true)) {
			throw new RuntimeException('SubscriptionBulkInvalidDate');
		}
		// Explicit business exception for 1d; other units keep their duration rule.
		$end = ($unit === 'd' && (float) $delay === 1.0) ? self::subscriptionCalendarShift($date, 1, 'd') : self::subscriptionCalendarShift(self::subscriptionCalendarShift($date, $delay, $unit), -1, 'd');
		if ($end <= self::subscriptionCalendarShift($date, 0, 'd')) {
			throw new RuntimeException('SubscriptionBulkEndNotAfterStart');
		}
		return $end;
	}

	/**
	 * Suggest a start from configuration, previous membership, validation, or today.
	 * @param int $now Reference date for configured offsets
	 * @return int Suggested start at midnight
	 */
	public static function subscriptionStartDateForBatch($member, $now)
	{
		require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
		$date = $member->datefin > 0 ? self::subscriptionCalendarShift($member->datefin, 1, 'd') : ($member->datevalid > 0 ? $member->datevalid : $now);
		$startOffsetSpec = getDolGlobalString('MEMBER_SUBSCRIPTION_START_AFTER');
		if ($startOffsetSpec) {
			if (!preg_match('/^([+-]?\d+)([dwmyY])$/D', $startOffsetSpec, $parts)) {
				throw new RuntimeException('SubscriptionBulkInvalidDate');
			}
			$date = self::subscriptionCalendarShift($now, (int) $parts[1], dol_strtolower($parts[2]));
		}
		// Keep the configured input timezone, as with the other batch date selectors.
		$correction = getDolGlobalString('MEMBER_SUBSCRIPTION_START_FIRST_DAY_OF');
		if ($correction === 'm') {
			$date = dol_mktime(0, 0, 0, (int) dol_print_date($date, '%m'), 1, (int) dol_print_date($date, '%Y'));
		} elseif ($correction === 'Y') {
			$date = dol_mktime(0, 0, 0, 1, 1, (int) dol_print_date($date, '%Y'));
		} elseif ($correction === '3m') {
			// Match the existing subscription form for members with a previous end.
			$date = self::subscriptionCalendarShift($member->datefin > 0 ? $member->datefin : $date, -3, 'm');
			$date = dol_mktime(0, 0, 0, (int) dol_print_date($date, '%m'), 1, (int) dol_print_date($date, '%Y'));
		}
		return dol_mktime(0, 0, 0, (int) dol_print_date($date, '%m'), (int) dol_print_date($date, '%d'), (int) dol_print_date($date, '%Y'));
	}

	/** Parse the raw V22 duration; an omitted or zero quantity means one. */
	public static function duration($type)
	{
		$raw = $type->duration ?? null;
		if ($raw === null || $raw === '') {
			// Preserve the reference's default for an entirely unconfigured type.
			return array(1, 'y');
		}
		if (!is_string($raw) || !preg_match('/^(0|[1-9][0-9]*)?([dwmy])$/D', $raw, $parts)) {
			throw new RuntimeException('SubscriptionBulkInvalidDate');
		}
		// V22 adherents/subscription.php defaults an empty duration_value to one.
		return array($parts[1] === '' || $parts[1] === '0' ? 1 : (int) $parts[1], $parts[2]);
	}
}
