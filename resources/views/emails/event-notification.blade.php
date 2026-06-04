<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Напоминание о событии</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #4f46e5;">Напоминание о событии</h1>
        
        <p>Привет!</p>
        
        <p>Напоминаем вам о предстоящем событии:</p>
        
        <div style="background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h2 style="margin-top: 0; color: #1e293b;">{{ $title }}</h2>
            <p style="margin: 10px 0;">
                <strong>Начало:</strong> {{ $start }}<br>
                <strong>Окончание:</strong> {{ $end }}
            </p>
        </div>
        
        <p>Не забудьте подготовиться к событию!</p>
        
        <p style="font-size: 12px; color: #64748b;">
            Это автоматическое уведомление. Пожалуйста, не отвечайте на это письмо.
        </p>
    </div>
</body>
</html>
