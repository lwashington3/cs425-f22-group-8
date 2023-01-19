from washOTP import TOTP


class WCS_QR(TOTP):
	def svg_uri(self, username:str) -> str:
		from io import BytesIO
		from qrcode.image.styledpil import StyledPilImage
		from qrcode.image.styles.moduledrawers import RoundedModuleDrawer
		from qrcode.image.styles.colormasks import RadialGradiantColorMask
		from qrcode.image.svg import SvgPathImage

		buf = BytesIO()

		self.qr("WCS Banking", username, save=buf,
		   image_factory=SvgPathImage, module_drawer=RoundedModuleDrawer(),
		   color_mask=RadialGradiantColorMask(back_color=(255, 255, 255, 0), center_color=(0, 0, 0, 255),
											  edge_color=(0, 0, 255, 255)))

		svg = buf.getvalue().decode("UTF-8")

		# https://stackoverflow.com/questions/66717659/encode-a-svg-image-in-python-like-javascript-encodeuricomponent-does
		enc_chars = '"%#{}<>'  # Encode these to %hex
		enc_chars_maybe = '&|[]^`;?:@='  # Add to enc_chars on exception
		svg_enc = ''
		for c in str(svg):
			if c in enc_chars:
				if c == '"':
					svg_enc += "'"
				else:
					svg_enc += '%' + format(ord(c), "x")
			else:
				svg_enc += c
		return "data:image/svg+xml," + ' '.join(svg_enc.split())  # Compact whitespace


def main(args:list):
	from argparse import ArgumentParser

	parser = ArgumentParser(prog="WCS Banking API", description="Creates QR codes for WCS customers to scan.")

	parser.add_argument("secret_key")
	parser.add_argument("username")
	parser.add_argument("-d", "--digits", type=int, default=6)
	parser.add_argument("-t", "--period", type=int, default=30)
	parser.add_argument("-a", "--algorithm", default="sha1")

	args = parser.parse_args(args)

	print(WCS_QR(args.secret_key,
				 digits=args.digits,
				 period=args.period,
				 algo=args.algorithm).svg_uri(args.username))


def test():
	code = "ACAHAACAAJGIJLAOC"
	otp = WCS_QR(code)
	uri = otp.svg_uri()

	with open("test.html", 'w') as f:
		f.writelines(f"""<html lang="en"><head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=yes">
		<title>WCS Authentication</title>
		<link href="/css/wcss.php" type="text/css" rel="stylesheet">
		<link rel="icon" type="image/x-icon" href="<?php echo FAVICON_LINK; ?>">
		<style>
			div.qr {{
				content: url("{uri}");
				filter: invert(50%)sepia(100%)saturate(10000%)
			}}
		</style>
	</head>
	<body>
	<h1>Scan Me</h1>
	<div class="container">
	    <!--<img src="{uri}">-->
	    <div class="qr">Hello</div>

	</div>
	<hr>
	<p>If you cannot scan me, enter the code manually</p>
	<code>
	    {code}
	</code>

	</body></html>
			""")


if __name__ == "__main__":
	from sys import argv
	main(argv[1:])
