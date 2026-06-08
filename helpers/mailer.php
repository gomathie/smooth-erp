<?php

function env_value($key, $default = '') {
    static $env = null;

    if ($env === null) {
        $env = [];
        $path = dirname(__DIR__) . '/.env';

        if (is_file($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                if (preg_match('/^(["\'])(.*)\1$/', $value, $matches)) {
                    $value = $matches[2];
                }

                $env[$name] = $value;
            }
        }
    }

    return $env[$key] ?? $default;
}

function smtp_expect($socket, $codes) {
    $response = '';

    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }

    foreach ((array)$codes as $code) {
        if (str_starts_with($response, (string)$code)) {
            return $response;
        }
    }

    throw new RuntimeException(trim($response));
}

function smtp_command($socket, $command, $codes) {
    fwrite($socket, $command . "\r\n");
    return smtp_expect($socket, $codes);
}

function send_smtp_mail($toEmail, $toName, $subject, $htmlBody, $textBody = '') {
    $host = env_value('SMTP_HOST', 'smtp.example.com');
    $port = (int)env_value('SMTP_PORT', '587');
    $username = env_value('SMTP_USERNAME', 'username@example.com');
    $password = env_value('SMTP_PASSWORD', 'change-me');
    $encryption = strtolower(env_value('SMTP_ENCRYPTION', 'tls'));
    $fromEmail = env_value('SMTP_FROM_EMAIL', 'no-reply@example.com');
    $fromName = env_value('SMTP_FROM_NAME', 'Smooth ERP');
    $timeout = (int)env_value('SMTP_TIMEOUT', '15');
    $debug = filter_var(env_value('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

    if ($host === '' || $username === '' || $password === '') {
        throw new RuntimeException('SMTP settings are incomplete.');
    }

    $remote = ($encryption === 'ssl' ? 'ssl://' : '') . $host . ':' . $port;
    $socket = stream_socket_client($remote, $errno, $errstr, $timeout, STREAM_CLIENT_CONNECT);

    if (!$socket) {
        throw new RuntimeException("SMTP connection failed: {$errstr}");
    }

    stream_set_timeout($socket, $timeout);

    try {
        smtp_expect($socket, 220);
        smtp_command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);

        if ($encryption === 'tls') {
            smtp_command($socket, 'STARTTLS', 220);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('SMTP TLS negotiation failed.');
            }
            smtp_command($socket, 'EHLO ' . ($_SERVER['SERVER_NAME'] ?? 'localhost'), 250);
        }

        smtp_command($socket, 'AUTH LOGIN', 334);
        smtp_command($socket, base64_encode($username), 334);
        smtp_command($socket, base64_encode($password), 235);

        smtp_command($socket, 'MAIL FROM:<' . $fromEmail . '>', 250);
        smtp_command($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtp_command($socket, 'DATA', 354);

        $boundary = 'pos_' . bin2hex(random_bytes(12));
        $encodedSubject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
        $safeFromName = addcslashes($fromName, "\\\"");
        $safeToName = addcslashes($toName ?: $toEmail, "\\\"");
        $textBody = $textBody ?: strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlBody));

        $message = [
            'From: "' . $safeFromName . '" <' . $fromEmail . '>',
            'To: "' . $safeToName . '" <' . $toEmail . '>',
            'Subject: ' . $encodedSubject,
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            '',
            '--' . $boundary,
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $textBody,
            '',
            '--' . $boundary,
            'Content-Type: text/html; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $htmlBody,
            '',
            '--' . $boundary . '--',
            '.',
        ];

        fwrite($socket, implode("\r\n", $message) . "\r\n");
        smtp_expect($socket, 250);
        smtp_command($socket, 'QUIT', 221);
    } catch (Throwable $exception) {
        @fwrite($socket, "QUIT\r\n");
        fclose($socket);

        if ($debug) {
            throw $exception;
        }

        throw new RuntimeException('Reset email could not be sent.');
    }

    fclose($socket);
    return true;
}

function send_password_reset_email($user, $resetLink) {
    $toEmail = trim($user['email'] ?? '');

    if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $toName = trim($user['name'] ?? $user['user'] ?? '');
    $appName = env_value('APP_NAME', 'Smooth ERP');
    $subject = $appName . ' password reset';
    $safeName = h($toName ?: 'there');
    $safeLink = h($resetLink);

    $html = '<p>Hello ' . $safeName . ',</p>'
        . '<p>Use the button below to reset your password. This link expires in 1 hour.</p>'
        . '<p><a href="' . $safeLink . '" style="display:inline-block;background:#00a65a;color:#fff;padding:10px 16px;text-decoration:none;">Reset password</a></p>'
        . '<p>If the button does not work, copy this link:<br><span style="word-break:break-all;">' . $safeLink . '</span></p>'
        . '<p>If you did not request this, ignore this email.</p>';

    $text = "Hello " . ($toName ?: 'there') . ",\n\n"
        . "Reset your password with this link. It expires in 1 hour:\n"
        . $resetLink . "\n\n"
        . "If you did not request this, ignore this email.";

    return send_smtp_mail($toEmail, $toName, $subject, $html, $text);
}
