<?php
/* Copyright (C) 2026 iLLixM <iLLixM@proton.me>
 * SPDX-License-Identifier: GPL-3.0-or-later
 *
 * CF-03 private one-shot evidence store. No database operations or HTTP.
 * No implicit evidence-root discovery and no retry or artifact overwrite.
 */
final class Cf03Evidence
{
    const DEFAULT_ROOT = '/var/lib/dolibarr-test-results';

    public static function need($condition, $reason)
    {
        if (!$condition) { throw new RuntimeException($reason); }
    }

    public static function absolute($path)
    {
        self::need(is_string($path) && strlen($path) <= 255
            && preg_match('~^/[a-zA-Z0-9_./-]+$~D', $path) === 1
            && strpos($path, '//') === false
            && !preg_match('~(?:^|/)\.{1,2}(?:/|$)~', $path),
            'CF03UnsafeEvidenceRoot');
        return $path;
    }

    public static function choice($environment, $dir, $suppliedRoot)
    {
        self::need(in_array($environment, array('A','B','C','D'), true),
            'CF03UnknownEnvironment');
        $root = $environment === 'D' ? self::absolute($suppliedRoot)
            : self::DEFAULT_ROOT;
        self::need($environment === 'D' || $suppliedRoot === null,
            'CF03EvidenceRootNotPermitted');
        self::absolute($dir);
        self::need(dirname($dir) === $root
            && preg_match('/^cf03-'.strtolower($environment)
                .'-[a-z0-9-]{6,40}$/D', basename($dir)) === 1,
            'CF03UnsafeEvidencePath');
        return $root;
    }

    private static function within($path, $root)
    {
        return $path === $root || strpos($path, rtrim($root, '/').'/') === 0;
    }

    public static function privateRoot($root, $environment, $documentRoot, $checkout)
    {
        self::absolute($root);
        clearstatcache(true, $root);
        self::need(is_dir($root) && !is_link($root)
            && realpath($root) === $root && is_readable($root)
            && is_writable($root), 'CF03EvidenceParentUnavailable');
        // Same trust boundary for A/B/C and D: a writable public parent can
        // replace the case directory even when its own mode is 0700.
        self::need((fileperms($root) & 0077) === 0
            && function_exists('posix_geteuid')
            && fileowner($root) === posix_geteuid(),
            'CF03EvidenceRootNotPrivate');
        if ($environment === 'D') {
            $doc = realpath($documentRoot);
            $source = realpath($checkout);
            self::need($doc !== false && $source !== false,
                'CF03EvidenceIsolationUnknown');
            // Permit an owner-private sibling of htdocs on jailed hosting,
            // but never the site root itself, inside htdocs or an ancestor
            // of the webroot or source checkout. Public alias exposure must
            // additionally be ruled out by the hosting operator.
            self::need($root !== dirname($doc)
                && !self::within($root, $doc)
                && !self::within($root, $source)
                && !self::within($doc, $root)
                && !self::within($source, $root),
                'CF03EvidenceInsideWebOrSource');

        }
    }

    public static function directory($dir, $create, $environment, $root, $doc, $checkout)
    {
        self::need(self::choice($environment, $dir,
            $environment === 'D' ? $root : null) === $root,
            'CF03UnsafeEvidencePath');
        self::privateRoot($root, $environment, $doc, $checkout);
        clearstatcache(true, $dir);
        if ($create) {
            self::need(!file_exists($dir) && !is_link($dir),
                'CF03EvidenceExistsNoRetry');
            self::need(@mkdir($dir, 0700, false), 'CF03EvidenceDirectoryUnavailable');
        }
        self::need(is_dir($dir) && !is_link($dir)
            && realpath($dir) === $dir
            && (fileperms($dir) & 0077) === 0
            && is_readable($dir) && is_writable($dir),
            'CF03UnsafeEvidenceDirectory');
        if (function_exists('posix_geteuid')) {
            self::need(fileowner($dir) === posix_geteuid(),
                'CF03EvidenceDirectoryWrongOwner');
        }
    }

    public static function verifyFile($dir, $filename)
    {
        self::need(in_array($filename,
            array('baseline.json', 'baseline.sha256'), true),
            'CF03UnexpectedEvidenceFile');
        $path = $dir.'/'.$filename;
        clearstatcache(true, $path);
        self::need(is_file($path) && !is_link($path)
            && (fileperms($path) & 0077) === 0
            && filesize($path) <= 524288
            && is_readable($path), 'CF03UnsafeBaselineFile');
        if (function_exists('posix_geteuid')) {
            self::need(fileowner($path) === posix_geteuid(),
                'CF03UnsafeBaselineFile');
        }
        return $path;
    }

    public static function readyToVerify($dir)
    {
        $entries = scandir($dir);
        self::need($entries !== false, 'CF03EvidenceReadUnavailable');
        sort($entries);
        self::need($entries === array('.', '..', 'baseline.json', 'baseline.sha256'),
            'CF03EvidenceExistsNoRetry');
        $data = self::verifyFile($dir, 'baseline.json');
        $digest = self::verifyFile($dir, 'baseline.sha256');
        $sha = trim((string)file_get_contents($digest));
        self::need(preg_match('/^[a-f0-9]{64}$/D', $sha) === 1
            && hash_equals($sha, hash_file('sha256', $data)),
            'CF03BaselineIntegrityMismatch');
        $raw = file_get_contents($data);
        $decoded = json_decode($raw, true);
        self::need(is_array($decoded) && json_last_error() === JSON_ERROR_NONE,
            'CF03BaselineMalformed');
        return $decoded;
    }

    /**
     * Admit a complete, valid case and seal this ONE verify attempt before
     * calling cf03Configure() or touching DB/Source/HTTP-dependent code.
     * An invalid baseline remains STOP without any business evaluation.
     */
    public static function startVerify($dir, $environment, $root,
        $documentRoot, $checkout, $validate)
    {
        self::need(is_callable($validate), 'CF03InvalidVerifyValidator');
        self::directory($dir, false, $environment, $root,
            $documentRoot, $checkout);
        $baseline = self::readyToVerify($dir);
        $validate($baseline);
        self::exclusive($dir, 'verify.started', gmdate('c')."\n");
        return $baseline;
    }

    public static function exclusive($dir, $filename, $value)
    {
        self::need(in_array($filename, array('baseline.json', 'baseline.sha256',
            'verify.started', 'after.json', 'verdict.json'), true)
            && is_string($value) && strlen($value) <= 524288,
            'CF03EvidenceWriteForbidden');
        $path = $dir.'/'.$filename;
        $fp = @fopen($path, 'x');
        self::need($fp !== false, 'CF03EvidenceExistsNoRetry');
        try {
            $len = strlen($value);
            $written = 0;
            while ($written < $len) {
                $n = fwrite($fp, substr($value, $written));
                self::need(is_int($n) && $n > 0, 'CF03EvidenceWriteFailure');
                $written += $n;
            }
            self::need(fflush($fp), 'CF03EvidenceWriteFailure');
        } finally {
            fclose($fp);
        }
        self::need(chmod($path, 0600), 'CF03EvidenceWriteFailure');
    }

    public static function json($dir, $filename, array $data)
    {
        self::exclusive($dir, $filename,
            json_encode($data, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR)."\n");
    }
}
