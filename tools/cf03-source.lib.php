<?php
/* Copyright (C) 2026 iLLixM
 * SPDX-License-Identifier: GPL-3.0-or-later
 * Bounded runtime source attestation for CF-03. No DB/HTTP or writes.
 */
final class Cf03SourceManifest
{
    public static function need($condition, $reason)
    {
        if (!$condition) { throw new RuntimeException($reason); }
    }

    /**
     * Complete PHP inventory in module production entrypoint/class/lib/admin/
     * module-descriptor directories. Not a complete ZIP distribution manifest.
     * This also detects new PHP files added to these directories.
     */
    public static function modulePaths($directory)
    {
        $root=realpath($directory);
        self::need($root!==false && is_dir($root), 'CF03SourceUnavailable');
        $paths=array('subscription.php');
        foreach (array('class','lib','admin','core/modules') as $relative) {
            $dir=$root.'/'.$relative;
            self::need(is_dir($dir) && !is_link($dir), 'CF03SourceUnavailable');
            $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
                $dir, FilesystemIterator::SKIP_DOTS));
            foreach ($it as $info) {
                self::need(!$info->isLink(), 'CF03SourceUnavailable');
                if ($info->isFile() && preg_match('/\.php$/D',$info->getFilename())) {
                    $paths[]=substr($info->getPathname(),strlen($root)+1);
                }
            }
        }
        sort($paths,SORT_STRING);
        self::need(count($paths)===count(array_unique($paths))
            && count($paths)>=20 && count($paths)<=150, 'CF03SourceManifestUnexpected');
        return $paths;
    }

    /** Explicit, reviewed Core critical execution path; not the entire Core tree. */
    public static function corePaths($coreVersion, $scenario)
    {
        $version=($coreVersion==='22.0.5')?'filefunc.inc.php':
            (($coreVersion==='24.0.3')?'version.inc.php':null);
        self::need($version!==null, 'CF03CoreVersionPolicyUnsupported');
        self::need(in_array($scenario,array('preflight','contribution',
            'invoice-existing','no-change'),true), 'CF03InvalidScenario');
        $paths=array($version,
            'core/db/mysqli.class.php',
            'core/class/commonobject.class.php',
            'user/class/user.class.php',
            'adherents/class/adherent.class.php',
            'adherents/class/subscription.class.php',
            'comm/action/class/actioncomm.class.php',
            'core/triggers/interface_50_modAgenda_ActionsAuto.class.php');
        if (in_array($scenario,array('preflight','invoice-existing'),true)) {
            $paths=array_merge($paths,array(
                'societe/class/societe.class.php',
                'compta/facture/class/facture.class.php',
                'compta/facture/class/paymentterm.class.php',
                'product/class/product.class.php'));
        }
        sort($paths,SORT_STRING);
        return $paths;
    }

    public static function hashes($root, array $paths, $reason)
    {
        self::need(is_dir($root), $reason);
        $out=array();
        foreach ($paths as $path) {
            self::need(is_string($path)
                && preg_match('~^[a-zA-Z0-9_./-]+$~D',$path)===1
                && strpos($path,'..')===false, $reason);
            $file=rtrim($root,'/').'/'.$path;
            self::need(is_file($file) && !is_link($file),$reason);
            $hash=@hash_file('sha256',$file);
            self::need(is_string($hash) && strlen($hash)===64,$reason);
            $out[$path]=$hash;
        }
        ksort($out,SORT_STRING);
        return $out;
    }

    public static function deployed($checkout, $module)
    {
        $paths=self::modulePaths($checkout);
        // Deployed copies must expose the SAME entire selected PHP inventory.
        self::need(self::modulePaths($module)===$paths,
            'CF03DeployedSourceMismatch');
        $source=self::hashes($checkout,$paths,'CF03SourceUnavailable');
        $installed=self::hashes($module,$paths,'CF03DeployedSourceMissing');
        self::need($source===$installed,'CF03DeployedSourceMismatch');
        return $installed;
    }

    public static function fullModuleSource($checkout)
    {
        $paths=self::modulePaths($checkout);
        $files=self::hashes($checkout,$paths,'CF03SourceUnavailable');
        foreach (array('tools/cf03-lab.php','tools/cf03-lab.lib.php',
            'tools/cf03-evidence.lib.php','tools/cf03-source.lib.php') as $path) {
            $files[$path]=self::hashes($checkout,array($path),
                'CF03SourceUnavailable')[$path];
        }
        ksort($files,SORT_STRING);
        return $files;
    }
}
