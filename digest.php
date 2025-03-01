<?php
    /*
        ImageDataDupes
        Version 0.0.1 a.k.a. "Callopistromyia annulipes" 
    */
    
    $config = [];
    
    // CONFIG START - FIDDLE HERE
    $config['recursiveMode'] = 1; 
    // 0 - non-recursive
    // 1 - recursive, all sub-directories, EXCEPT dirs starting with dot
    // 2 - recursive, ALL sub-directories, INCLUDING dirs with trailing dot
    
    
    $config['ext'] = 'cr2,jpg,jpeg';
    // comma separated of extension to check, case insensitive.
    // if false, check all files
    
    $config['path'] = isset($argv[1]) ? $argv[1] : ".";
    // starting path. Use one from command line parameter. If not given, 
    //use current directory
    
    $config['slashMode'] = 0;
    // Slash style (Windows vs. rest)
    // Windows will recognize Linux style, with exception of copy/paste path 
    // into cmd line to quickly run file - if there are POSIX slashes on path
    // it won't open the file. Possible values:    
    // 0 - auto (recommended)
    // 1 - Linux style (bar/foo)
    // 2 - Windows (bar\foo)
    
    $config['ignoreSymbolicLinks'] = true;
    // self-explanatory - if true, it doesn't check symbolic links
    
    $config['cacheFile'] = 'digest.txt';
    // CONFIG END
    
    // SN#185/v0.3.1/rev. 5
    
    define('S_FG_BLACK', "\e[30m");
    define('S_FG_RED', "\e[31m");
    define('S_FG_GREEN', "\e[32m");
    define('S_FG_YELLOW', "\e[33m");
    define('S_FG_BLUE', "\e[34m");
    define('S_FG_MAGENTA', "\e[35m");
    define('S_FG_CYAN', "\e[36m");
    define('S_FG_WHITE', "\e[37m");
    define('S_FG_BRIGHT_BLACK', "\e[90m");
    define('S_FG_BRIGHT_RED', "\e[91m");
    define('S_FG_BRIGHT_GREEN', "\e[92m");
    define('S_FG_BRIGHT_YELLOW', "\e[93m");
    define('S_FG_BRIGHT_BLUE', "\e[94m");
    define('S_FG_BRIGHT_MAGENTA', "\e[95m");
    define('S_FG_BRIGHT_CYAN', "\e[96m");
    define('S_FG_BRIGHT_WHITE', "\e[97m");
    
    // 2) background
    define('S_BG_BLACK', "\e[40m");
    define('S_BG_RED', "\e[41m");
    define('S_BG_GREEN', "\e[42m");
    define('S_BG_YELLOW', "\e[43m");
    define('S_BG_BLUE', "\e[44m");
    define('S_BG_MAGENTA', "\e[45m");
    define('S_BG_CYAN', "\e[46m");
    define('S_BG_WHITE', "\e[47m");
    define('S_BG_BRIGHT_BLACK', "\e[100m");
    define('S_BG_BRIGHT_RED', "\e[101m");
    define('S_BG_BRIGHT_GREEN', "\e[102m");
    define('S_BG_BRIGHT_YELLOW', "\e[103m");
    define('S_BG_BRIGHT_BLUE', "\e[104m");
    define('S_BG_BRIGHT_MAGENTA', "\e[105m");
    define('S_BG_BRIGHT_CYAN', "\e[106m");
    define('S_BG_BRIGHT_WHITE', "\e[107m");
    
    // 3) special styles
    
    // switches fg with bg. Works one time, so using two S_REVERSE won't get to
    // the starting point. You have to use alternating S_REVERSE and S_UNREVERSE
    define('S_REVERSE', "\e[7m");
    define('S_UNREVERSE', "\e[27m"); // switches fg with bg (works one time)
    define('S_UNDERLINE', "\e[4m");
    define('S_NOUNDERLINE', "\e[24m");
    
    // for some reason it won't make font bold, it just "boosts" color from
    // normal version to bright
    define('S_BOLD', "\e[1m");
    define('S_END', "\e[0m");
    define('CL_CRIT', 0);
    define('CL_INFO', 1);
    define('CL_WARN', 2);
    define('CL_DBUG', 3);
    define('CL_OKAY', 4);
    
    $params = '';    
    $eol = PHP_EOL;
    $hr = str_repeat('-=', 40);
    exec('exiftool 2>&1', $null, $exit);
    
    if ($exit !== 0) clog("Can\'t execute exiftool. Make sure it's in your path directory. If you don't have it installed, get one from:{$eol}https://exiftool.org/ (official)${eol}https://oliverbetz.de/pages/Artikel/ExifTool-for-Windows (unofficial build, Windows tuned)", 0);
    
    switch ($config['recursiveMode']) {
        case 1: $params .= ' -r'; break;
        case 2: $params .= ' -r.'; break;
        default: $params .= ''; break;
    }
    if ($config['ext']) $params .= ' ' . implode(' ', array_map(fn($ext) => "-ext $ext", explode(',', $config['ext'])));
    if (PHP_OS_FAMILY === "Windows" && $config['slashMode'] === 0) $config['slashMode'] = 2;
    if (!file_exists($config['cacheFile'])) {
        $cmd = "exiftool {$params} -q -q -m -p " . '"$filepath|$imagedatamd5"' . " {$config['path']} > " . $config['cacheFile'];
        clog("First run, getting data. It might take a long time...{$eol}You may stepp your tea now (1-3 minutes for green, about 5 for black){$eol}");
        exec($cmd);
        
    } else clog(['Using cache file %s', $config['cacheFile']]);
    
    $data = trim(file_get_contents($config['cacheFile']));
    if ($config['slashMode'] == 2) $data = str_replace('/', "\\", $data); 
    foreach (explode("\r\n", $data) as $line) {
        list($path, $digest) = explode("|", $line);
        if (file_exists($path) && ($config['ignoreSymbolicLinks'] && !is_link($path)) && $digest) $out[$path] = $digest;
        
    }
    clog(["Getting data done! Found %d files, checking for duplicates", count($out)]);
    echo $hr . $eol. $eol;
    $dcG = 0;
    $dc = 0;
    foreach ($out as $path => $digest) {
        if ($dupes = getDupes($digest, $out, $path)) { 
            $dcG++;
            $dc += count($dupes);
            $hash = md5_file($path); 
            echo 'Duplicates of: ' . S_FG_BRIGHT_WHITE . $path . S_END . PHP_EOL;
            foreach ($dupes as $dupe) {
                
                $dhash = md5_file($dupe); 
                
                $sizediff = filesize($path) - filesize($dupe);
                $diffcolor = ($sizediff > 0) ? S_FG_BRIGHT_GREEN : S_FG_BRIGHT_YELLOW;
                // \/ checks if file is identical (by MD5 hash) or different (then shows byte difference).
                if ($sizediff === 0 && $hash !== $dhash) {
                    $identical = '[' . S_FG_BRIGHT_CYAN . 'different, same size' . S_END. ']';
                } else $identical = $hash === $dhash ? '[' . S_FG_BRIGHT_RED . 'identical' . S_END . ']' : sprintf('[' . S_FG_BRIGHT_MAGENTA . 'different ' . S_END . '(%s%+db%s)]', $diffcolor, $sizediff, S_END);
                echo ' * ' . $dupe . " {$identical}" . PHP_EOL;
                unset($out[$dupe]);
            }
            unset($out[$path]);
            echo $hr . $eol. $eol;
        }
    }
    echo $eol;
    clog(['%s duplicates found in %s groups', S_FG_BRIGHT_RED . $dc . S_END, S_FG_BRIGHT_RED . $dcG . S_END]);
    
    // specialized array search
    function getDupes($needle, $haystack, $checkKey = false) {
        $out = [];
        foreach ($haystack as $k => $v) {
            if (($v == $needle)) {
                if (($checkKey && $k !== $checkKey) || !$checkKey) $out[] = $k;
            }
        }
        if (count($out) === 0) return false; else return $out;
    }                                        
    
    /**
        * Console logging function with Win 10 (and older with ansicon extenstion) ANSI color support: clog
        *
        * @param mixed $msg     The message to be logged. If it's an array, it is treated as arguments for sprintf.
        * @param int   $status  The status of the log message (0-4). CL_CRIT additionally interrupts the program.
        * @param bool  $stdlog  Whether to trigger a PHP error based on the log status.
        * @param bool  $eol     Insert EOL at the end
        *
        * @return mixed The logged message.
    */
    function clog($msg, $status = 1, $stdlog = false, $eol = true) {
        global $dbg;
        if ($status === 3 && !$dbg) return;
        $colors = [ // array of statuses - BG, FG (if 0 then use bg as fg color), message
        0 => [41, 0, 'CRIT'],
        1 => [46, 0, 'INFO'],
        2 => [43, 0, 'WARN'],
        3 => [100, 0,'DBUG'],
        4 => [42, 0, 'OKAY']
        ];
        $codes = [CL_CRIT => E_USER_ERROR, CL_WARN => E_USER_WARNING, CL_DBUG => E_USER_NOTICE]; // translation table for stdlog
        
        if ($colors[$status][1] === 0) $colors[$status][1] = $colors[$status][0] - 10; // bg color to fg
        $esc = chr(27);
        $end = $esc . '[0m';
        if (is_array($msg)) $msg = call_user_func_array('sprintf', $msg);
        echo "{$esc}[{$colors[$status][0]}m[{$colors[$status][2]}]{$end} {$esc}[{$colors[$status][1]}m{$msg}{$end}" . ($eol ? PHP_EOL : '');
        if ($stdlog && array_key_exists($status, $codes)) trigger_error($msg, $codes[$status]);
        if ($status === 0) die(1);
        return $msg;
    }            