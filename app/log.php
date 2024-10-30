<?php
// ログレベル
enum LogLevel: string {
    case VERBOSE = 'V';
    case DEBUG = 'D';
    case INFO = 'I';
    case WARNING = 'W';
    case ERROR = 'E';
    case FATAL = 'F';
}

function isErrorOrFatal(string $logLevel): bool {
    return match (getLogLevelByString($logLevel)) {
        LogLevel::ERROR, LogLevel::FATAL => true,
        default => false,
    };
}

function isLogLevel(string $logLevel): bool {
    return match ($logLevel) {
        'V', 'D', 'I', 'W', 'E', 'F' => true,
        default => false,
    };
}

function getLogLevelByString(string $logLevel): LogLevel {
    return match ($logLevel) {
        'V' => LogLevel::VERBOSE,
        'D' => LogLevel::DEBUG,
        'I' => LogLevel::INFO,
        'W' => LogLevel::WARNING,
        'E' => LogLevel::ERROR,
        'F' => LogLevel::FATAL,
        default => throw new InvalidArgumentException("Invalid log level: $logLevel"),
    };
}
$logFileName = $argv[1];
$appName = $argv[2] ?? null;
// ログファイルのパス
$logFilePath = '/var/www/html/log/' . $logFileName;
// 出力するCSVファイルのパス
$outputCsvPath = '/var/www/html/log/output.csv';

// ログファイルを読み込む
$logFile = fopen($logFilePath, 'r');
if (!$logFile) {
    die("ログファイルを開けませんでした。");
}

// 出力するCSVファイルを開く
$outputCsv = fopen($outputCsvPath, 'w');
if (!$outputCsv) {
    die("CSVファイルを開けませんでした。");
}

// CSVのヘッダーを書き込む
fputcsv($outputCsv, ['Timestamp', 'Process', 'PID', 'TID', 'Log Level','行数', 'Message']);
$index = 0;
$insertCsvData = [
];
$appProcessList = [];
// ログファイルを一行ずつ読み込む
while (($line = fgets($logFile)) !== false) {
    $index++;  
    // スペースで分割する
    $parts = preg_split('/\s+/', $line, 6);

    if (count($parts) < 6) {
        continue; // 不完全な行はスキップ
    }

    // TODO: バリデーションをいい感じにまとめたい
    $timestamp = $parts[0] . ' ' . $parts[1];
    $process = $parts[2];
    $pid = $parts[3];
    $tid = $parts[4];
    if (!is_numeric($tid) || !is_numeric($pid)) {
        continue; // $parts[4]が数字の文字列でない場合はスキップ
    }
    if(isset($parts[5][0]) && isLogLevel($parts[5][0])) {
        $logLevel = $parts[5][0];
    } else {
        continue; // 不正なログレベルはスキップ
    }
    $message = substr($parts[5], 2);
    if (isset($appName) && strpos($message, $appName) !== false) {
        // アプリ名の指定がある場合、アプリ名が含まれるエラーメッセージであればプロセスIDを記録
        $appProcessList[] = $pid;
    }
    if(isErrorOrFatal($logLevel)) {
        $insertCsvData[] = [
            'timestamp' => $timestamp,
            'process' => $process,
            'pid' => $pid,
            'tid' => $tid,
            'logLevel' => $logLevel,
            'index' => $index,
            'message' => $message,
        ];
    }
}

foreach ($insertCsvData as $data) {
    if(empty($appName)){
        // アプリ名の指定がない場合は全てのエラーログを出力
        fputcsv($outputCsv, [$data['timestamp'], $data['process'], $data['pid'], $data['tid'], $data['logLevel'], $data['index'], $data['message']]);
    } else if (in_array($data['pid'], $appProcessList)) {
        // アプリ名の指定がある場合は指定されたアプリ名が表示されるプロセスのエラーログを出力
        fputcsv($outputCsv, [$data['timestamp'], $data['process'], $data['pid'], $data['tid'], $data['logLevel'], $data['index'], $data['message']]);
    }
}


// ファイルを閉じる
fclose($logFile);
fclose($outputCsv);

echo "CSVファイルへの書き込みが完了しました。\n";
echo "/log/output.csv を確認してください。\n";
?>