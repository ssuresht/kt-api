<html>
<head>
</head>
<body>
    <p>
       Kotonaruにお問い合わせが届きました！<br>
        確認をお願いいたします。
    </p>

    <p>-----</p>

    <p>
        <span>会社名 / 学校名：{{ $content->company_or_school_name }}</span><br>
        <span>氏名：{{ $content->name }}</span><br>
        <span>メールアドレス：{{ $content->email }}</span><br>
         <span>電話番号：{{ $content->telephone }}</span><br>
        <span>お問い合わせ内容：{!! nl2br(e($content->inquiry_content)) !!}</span><br>
    </p>

    -----<br>

    <p>Kotonaru</p>
</body>
</html>
