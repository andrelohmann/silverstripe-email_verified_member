<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<style>
			p{
				font-size: 1.2em;
				color: #444;
			}
			p.comments{
				font-size: 1.4em;
				color: #222;
				padding: 20px;
			}
		</style>
	</head>
	<body>

		<p>$Member.Name,</p>

		<p><%t EmailVerifiedMember.CONFIRMEMAILSUBJECT "EmailVerifiedMember.CONFIRMEMAILSUBJECT" %> $BaseHref.</p>

                <p><a href="$ValidationLink">$ValidationLink</a></p>
	</body>
</html>