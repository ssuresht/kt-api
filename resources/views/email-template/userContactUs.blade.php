<html>
<head>
</head>
<body>
    <p>
        {{ $content->name }} 様
    </p>

    <p>
        Kotonaruへのお問い合わせを頂きありがとうございました。<br>
        以下の内容で送信いたしました。
    </p>

    <br>

    <p>
        <span>会社名 / 学校名：{{ $content->company_or_school_name }}</span><br>
        <span>氏名：{{ $content->name }}</span><br>
        <span>メールアドレス：{{ $content->email }}</span><br>
         <span>電話番号：{{ $content->telephone }}</span><br>
        <span>お問い合わせ内容：{!! nl2br(e($content->inquiry_content)) !!}</span><br>
    </p>

    -----<br>
        <br>
    <p>
        2営業日以内に、担当者よりご連絡いたします。<br>
        よろしくお願い致します
    </p>

    <br>
    <br>

    <p>
        本メールは、配信専用のアドレスで配信されています。
        このメールにご返信いただいても、内容の確認およびご返答はできません。ご了承ください。
        覚えがないのにこのメールを受け取られた方は、お手数ではございますがこのまま破棄をお願い致します。
    </p>

    <br>

    <p>Kotonaru</p>
</body>
</html>
