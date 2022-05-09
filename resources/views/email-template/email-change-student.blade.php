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
		{{ $data['student_family_name'] }} {{ $data['student_first_name'] }}様<br><br>
		いつもKotonaruを利用いただき、ありがとうございます。
		<br> <br>
		メールアドレスの変更は、まだ完了しておりません。<br>
		完了するには、下記ボタンからKotonaruにアクセスしてくださいませ。<br>
		アクセス有効期限は24時間です。<br>
		それ以降は、再度マイページからメールアドレスの変更をお願いいたします。<br>
		<br> <br>
		<a style="text-decoration:none !important; text-decoration:none;" href="{{ $data['url'] }}" class="button">ここをクリック</a>
		<br> <br>

		ボタンがうまく動作しない場合は下記のURLをコピーしてご利用ください。
		<br>
		{{ $data['url'] }}
		<br>
		<br>
		<br>

		本メールは、配信専用のアドレスで配信されています。<br>
		このメールにご返信いただいても、内容の確認およびご返答はできません。ご了承ください。<br>
		当サイトへの登録をした覚えがないのに、このメールを受け取られた方は、お手数ではございますがこのま <br>
		ま破棄をお願い致します。
		<br> <br>

		Kotonaru事務局

	</body>

	</html>