<?php

ini_set('memory_limit', '-1');

if (isset($_FILES['batchfile'])) {
    if ($_FILES['batchfile']['error'] == false && $_FILES['batchfile']['size'] <= 1000000 && strtolower(substr($_FILES['batchfile']['name'], -4)) == '.bat') { 
        // Ensure the file is a batch file and does not exceed 1MB

        if ($_POST['passage'] > 0 && $_POST['passage'] <= 20) {
            $passage = htmlspecialchars($_POST['passage']);
        } else {
            $passage = 1;
        }

        sleep(1);

        if (!is_dir('data')) mkdir('data', 0755);

        // Delete files older than 24 hours from the 'data' folder
        foreach (glob('data/' . "*") as $file) {
            if (filemtime($file) < time() - 86400) {
                unlink($file);
            }
        }

        // Save obfuscated batch file
        file_put_contents('data/' . $_FILES['batchfile']['name'], obfuscateBatchFile($_FILES['batchfile']['tmp_name'], $passage));

        echo '<br><br><center><a class="btn" style="padding-top: 15px;border-radius: 10px;" href="./data/' . $_FILES['batchfile']['name'] . '">Download <strong>' . $_FILES['batchfile']['name'] . '</strong> obfuscated x' . $passage . '</a></center>';

    } else {
        echo "<center><strong>Error: Invalid file!</strong><br>(Either no file attached, file exceeds 1MB, or not a batch file)</center>";
    }
}

/**
 * Function to obfuscate a batch file
 * 
 * @param string $batchfile The original batch file path
 * @param int $pass The number of obfuscation passes
 * @return string The obfuscated batch script
 */
function obfuscateBatchFile($batchfile, $pass = 1) {

    for ($i = 0; $i < $pass; $i++) {

        if ($i == 0) {
            $script = file_get_contents($batchfile); // First pass: get original script
        } else {
            $script = $batchfile_obfuscate; // Use previously obfuscated script for next passes
        }

        $batchfile_obfuscate = ''; // Reset previous obfuscation work

        // Character sets for obfuscation
        $stringVar0 = '@ 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $stringVar1 = '_ÄÅÇÉÑÖÜáàâäãåçéèêëíìîïñóòôöõúùûüabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $stringVar8 = 'abcdefghijklmnopqrstuvwxyz';
        $stringVar2 = '_¯-ஐ→あⓛⓞⓥⓔ｡°º¤εïз╬㊗⑪⑫⑬㊀㊁㊂のðabcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        // Generate variable names randomly
        $stringGen1 = substr(str_shuffle($stringVar1), 0, rand(3, 5)); 
        $stringGen2 = '';

        $arrayTable = array();
        $arrayVar0 = str_split($stringVar0);
        shuffle($arrayVar0);

        foreach ($arrayVar0 as $pos => $char) {
            $arrayTable[] = [$char, '%' . $stringGen1 . ':~' . $pos . ',1%'];
            $stringGen2 .= $char;
        }

        $arrayText = str_split($script);
        $convertWaitVar = false;
        $convertWaitLabel = false;
        $newLine = true; // Assume first line is a new line

        if ($i == $pass - 1) {
            $batchfile_obfuscate .= "\xFF\xFE" . '&@cls&';
        }
        $batchfile_obfuscate .= '@set "' . $stringGen1 . '=' . $stringGen2 . '"' . PHP_EOL;

        foreach ($arrayText as &$charOriginal) {

            if ($newLine == true && $charOriginal == ':') { // Detect label
                $convertWaitLabel = true;
            } 

            if ($charOriginal == "\n") {
                $newLine = true;
                $convertWaitVar = false;
                $convertWaitLabel = false;
            } else {
                $newLine = false;
            }

            if ($charOriginal == ' ') {
                $convertWaitLabel = false; // Labels cannot contain spaces
            }

            if ($convertWaitVar == false && ($charOriginal == '%' || $charOriginal == '!')) { 
                $convertWaitVar = true; // Start of variable
            } elseif ($convertWaitVar == true && ($charOriginal == '%' || $charOriginal == '!')) { 
                $convertWaitVar = false; // End of variable
                $convertWaitLabel = false;
            }

            if ($convertWaitVar == false && $convertWaitLabel == false && $newLine == false) {
                $convert = false;
                foreach ($arrayTable as list($char1, $char2)) {
                    if ($charOriginal == $char1) {
                        if (rand(1, 20) == 8) {
                            $batchfile_obfuscate .= $char2 . '%' . substr(str_shuffle($stringVar1), 3, 7) . '%'; 
                        } else {
                            $batchfile_obfuscate .= $char2;
                        }
                        $convert = true;
                    }
                }

                if ($convert == false) {
                    $batchfile_obfuscate .= $charOriginal; 
                }
            } else {
                $batchfile_obfuscate .= $charOriginal;
            }
        }
    }

    return html_entity_decode($batchfile_obfuscate);
}

?>
