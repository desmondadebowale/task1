<?php

function runScripts($path,$hngid, &$data)
{
    $fileType = pathinfo($path, PATHINFO_EXTENSION);

    $command = '';

    switch ($fileType) {
        case 'php':
            $command .= 'php ' . $path;
            break;
        case 'py':
            $command .= 'python3 ' . $path;
            break;
        case 'js':
            $command .= 'node ' . $path;
            break;
        default:
            break;
    }
    
    $output = [];
    $return_val = '';

    exec($command, $output, $return_val);

    if ($return_val == 0) {
        $result = testOutput($output);
        $temp = [
            'HNGID' => strtoupper($hngid),
            'Comment' => $output[0],
            'result' => $result,
        ];
        $data[] = $temp;
    }
}

function testOutput($output)
{
    $pattern = "/Hello World, this is .* with HNGi7 ID .* using .* for stage 2 task/";

    if (preg_match($pattern, $output[0])) {
        return "Passed";
    }
    return "Failed";
}

// Start of script

$scripts = scandir('./scripts');

foreach ($scripts as $script) {
    if (! in_array($script, ['.', '..'])) {
       $hngid= pathinfo($script, PATHINFO_FILENAME); 

        runScripts('./scripts/' . $script, $hngid, $data);
    }
}

$display = $_SERVER['QUERY_STRING'] ?? 'html';
$display = $display == 'json' ? 'json' : 'html';

if ($display == 'html') {
    echo '<h1>Task1</h1>';
    foreach ($data as $row) {
        ob_flush();
        flush();
        sleep(1);
        echo '<p>"HNGID": '.$row['HNGID'].',  "Comment": '. $row['Comment'].',  "Status":'.$row['result'].'</p>';
        

    }
} elseif ($display == 'json') {
    $json = json_encode($data, TRUE);
    header('Content-Type: application/json');
    echo $json;
}
