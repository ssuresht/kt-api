	<!DOCTYPE html>
	<html lang="en">

	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title>Document</title>
		<style>
			.button {
				font: bold 11px Arial;
				text-decoration: none;
				background-color: #41ABA3;
				color: #FFFFFF;
				padding: 5px 5px 5px 5px;
				border-top: 1px solid #41ABA3;
				border-right: 1px solid #41ABA3;
				border-bottom: 1px solid #41ABA3;
				border-left: 1px solid #41ABA3;
			}
		</style>
	</head>

	<body>
		オンラインインターン検索サイトKotonaruです！<br>
		この度はKotonaruへの会員登録依頼を頂きまして、ありがとうございます。
		<br> <br>
		こちらから会員登録のお手続きをお願いいたします。
		<br> <br>
		<a style="text-decoration:none !important; text-decoration:none;" href="{{ $data['url'] }}" class="button">ここをクリック</a>
		<br> <br>

		ボタンがうまく動作しない場合は下記のURLをコピーしてご利用ください。
		<br>
		{{ $data['url'] }}
		<br>
		<br>
		<br>

		本メールは、配信専用のアドレスで配信されています。このメールにご返信 いただいても、内容の確認およびご返答はできません。ご了承ください。<br>
		当サイトについて覚えがないのに、このメールを受け取られた方は、お手数ではございますがこのまま破棄をお願い致します。
		<br> <br>

		Kotonaru

	</body>

	</html>