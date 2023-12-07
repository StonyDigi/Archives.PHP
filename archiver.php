<?php
$archives = array();
$archives[] = array(
    'project' => 'default',
    'files' => array(
        'c:\\xampp\\htdocs\\default',
        'c:\\xampp\\apache\\conf\\vhosts\\000-default.conf'
    ),
    'databases' => array()
);
$archives[] = array(
    'project' => 'wordpress.webler',
    'files' => array(
        'c:\\xampp\\htdocs\\wordpress.webler',
        'c:\\xampp\\apache\\conf\\vhosts\\100-wordpress.webler.conf',
    ),
    'databases' => array(
        'wordpresswebler'
    )
);

// Beállítja az időt
$time = time();

// Ellenőrzi, hogy elérhető-e az IntlDateFormatter osztály
if (class_exists('IntlDateFormatter')) {
    $fmt = IntlDateFormatter::create(
        'hu_HU',
        IntlDateFormatter::FULL,
        IntlDateFormatter::FULL,
        'Europe/Budapest',
        IntlDateFormatter::GREGORIAN
    );
    echo $fmt->format($time), "\n";
} else {
    // Ha az IntlDateFormatter nem elérhető, akkor használja a strftime-t
    setlocale(LC_ALL, "hu_HU");
    echo strftime('%A', $time), "\n";
}
echo "Rendszergazda: Somossy László Files Archiver\n\n";
$weekdays = array(
    '7_VAS', '1_HET', '2_KED', '3_SZE',
    "4_CSU", "5_PEN", "6_SZO", "7_VAS"
);
$months = array('', // !
    '01_JAN', '02_FEB', '03_MAR', '04_APR',
    '05_MAJ', '06_JUN', '07_JUL', '08_AUG',
    '09_SZE', '10_OKT', '11_NOV', '12_DEC',
);
$actdir = array(
    $weekdays[date('w', $time)],
    $months[date('n', $time)]
);
$archdir = 'c:\\Users\\Administrator\\Documents\\Archive\\';
foreach ($archives as $archive) {
    $project = $archive['project'];
    $files = $archive['files'];
    $databases = $archive['databases'];
    foreach ($actdir as $dir) {
        $nowdir = $archdir . $project . '\\' . $dir;
        if (!del_tree($nowdir) && file_exists($nowdir)) unlink($nowdir);
        if (file_exists($nowdir)) {
            echo 'Nem sikerült menteni a ', $nowdir, " mappába!\n";
            break;
        };
        mkdir($nowdir, 0777, true);
        if (!file_exists($nowdir)) {
            echo 'Nem sikerült menteni a ', $nowdir, " mappába!\n";
            break;
        };
        if (count($databases)) {
            $dbdir = $nowdir . '\\' . 'db';
            mkdir($dbdir);
            foreach ($databases as $database) {
            $dumpFile = $dbdir . '\\' . $database . '_dump.sql';
			$escapedPassword = escapeshellarg('cRph@h5okyYrc7h-');
			$command = "mysqldump -uwordpresswebler -p$escapedPassword $database > \"$dumpFile\"";
			$output = null;
			$returnValue = null;
			exec($command, $output, $returnValue);

			// Kiírja a parancs kimenetét és a hibakódot
			$logMessage = "\n MySQL dump kimenet: \n\n" . implode("\n", $output) . "\n\n";
			$logMessage .= "MySQL dump hiba: $returnValue\n";

			// Log fájlba írás
			$logFile = 'error.log';
			file_put_contents($logFile, $logMessage, FILE_APPEND);

			if ($returnValue !== 0) {
				// Ha hiba történt, akkor kilép a scriptből
				die("MySQL dump hiba. További részletek a '$logFile' fájlban.\n");
			}
          }
        }
        for ($i = 0; $i < count($files); $i++) {
            $orig = $files[$i];
            $fdir = $nowdir . '\\' . 'files' . $i;
            mkdir($fdir);
            if (is_dir($orig)) {
                $command = "XCOPY $orig $fdir /S /E /C /Q /G /H";
                //echo $command . "\n";
                `$command`;
            } else {
                $command = 'COPY /V ' . $orig . ' ' . $fdir . '\\';
                //echo $command . "\n";
                `$command`;
            };
        };
    };
};

function del_tree($dir)
{
    if (!is_dir($dir)) return false;
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? del_tree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}
?>
