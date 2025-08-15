<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Create Account - Game Day Valet</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
	<style>
		:root { --primary:#c94c4c; --ink:#0f172a; --muted:#64748b; --border:#e2e8f0; --bg:#ffffff; }
		* { box-sizing: border-box; }
		body { margin: 0; background: var(--bg); color: var(--ink); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; }
		.navbar { border-bottom: 1px solid var(--border); background:#fff; }
		.navbar .inner { max-width: 1200px; margin: 0 auto; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; }
		.brand { font-weight: 800; letter-spacing: -.3px; color: var(--ink); text-decoration: none; font-size: 18px; }
		.nav { display: flex; gap: 18px; }
		.nav a { color: var(--muted); text-decoration: none; font-weight: 600; }
		.nav a:hover { color: var(--ink); }
		.page { max-width: 1200px; margin: 0 auto; padding: 48px 20px; display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 56px; align-items: center; }
		@media (max-width: 960px) { .page { grid-template-columns: 1fr; padding: 28px 16px; gap: 28px; } }
		.hero h1 { font-size: 42px; line-height: 1.1; margin: 0 0 12px; font-weight: 900; letter-spacing: -0.6px; }
		.hero p { color: var(--muted); font-size: 16px; max-width: 580px; }
		.hero-illustration { margin-top: 24px; border: 1px dashed var(--border); border-radius: 16px; padding: 28px; color: var(--muted); }
        .hero-illustration img { border-radius: 16px; height: 300px; object-fit: cover; }
		.card { width: 100%; max-width: 520px; border: 1px solid var(--border); border-radius: 16px; background: #fff; box-shadow: 0 8px 32px rgba(2,8,23,.06); padding: 24px; }
		.card h2 { margin: 0 0 6px; font-size: 24px; font-weight: 800; }
		.card .sub { margin: 0 0 18px; color: var(--muted); }
		.label { display: block; font-size: 12px; letter-spacing: .14em; color: #94a3b8; margin: 12px 0 8px; font-weight: 800; }
		.input { width: 100%; border: 1.6px solid var(--border); border-radius: 12px; padding: 12px 14px; font-size: 15px; outline: none; transition: border-color .2s, box-shadow .2s; }
		.input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgb(201 76 76 / 12%); }
		.row-two { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
		@media (max-width: 520px) { .row-two { grid-template-columns: 1fr; } }
		.inline { display: flex; align-items: center; gap: 10px; }
		.check { width: 18px; height: 18px; accent-color: var(--primary); }
		.link { color: var(--primary); text-decoration: none; font-weight: 700; }
		.btn-primary { width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 10px; border: 0; border-radius: 12px; padding: 14px; font-weight: 800; color: #fff; background: var(--primary); cursor: pointer; box-shadow: 0 14px 28px rgba(201,76,76,.18); }
		.sep { display: grid; grid-template-columns: 1fr auto 1fr; gap: 10px; align-items: center; color: #94a3b8; margin: 18px 0; }
		.sep::before, .sep::after { content: ""; height: 1px; background: var(--border); }
		.btn-google {
			width: 100%;
			border: 1.6px solid var(--border);
			border-radius: 12px;
			padding: 12px 14px;
			font-weight: 700;
			background: #fff;
			display: flex;
			gap: 10px;
			align-items: center;
			justify-content: center;
			cursor: pointer;
			text-decoration: none;
			color: var(--ink);
			transition: all 0.2s ease;
		}
		.btn-google:hover {
			background: #f8fafc;
			border-color: #cbd5e1;
			transform: translateY(-1px);
		}
		.small { color: var(--muted); font-size: 13px; text-align: center; margin-top: 12px; }
	</style>
</head>
<body>
	<header class="navbar">
		<div class="inner">
			<a class="brand" href="#">Game Day Valet</a>
			<nav class="nav">
				<a href="{{ route('rentalsystem.signup') }}">Create account</a>
				<a href="{{ route('rentalsystem.signin') }}">Sign in</a>
			</nav>
		</div>
	</header>

	<main class="page">
		<section class="hero">
			<h1>Create your account</h1>
			<p>Set up your profile to browse sports, view tournaments, and book rentals from a modern web dashboard.</p>
			<div class="hero-illustration">
                <img src="{{ asset('images/main-thumbnail1.jpg') }}" width="100%" alt="">
            </div>
		</section>

		<section>
			<div class="card">
				<h2>Sign up</h2>
				<p class="sub">Please fill the fields below</p>

				@if(session('success'))
					<div style="background:#ecfdf5;border:1px solid #10b98133;color:#065f46;padding:10px 12px;border-radius:10px;margin-bottom:10px;">{{ session('success') }}</div>
				@endif
				@if(session('error'))
					<div style="background:#fef2f2;border:1px solid #ef444433;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:10px;">{{ session('error') }}</div>
				@endif
				@if ($errors->any())
					<div style="background:#fef2f2;border:1px solid #ef444433;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:10px;">
						<ul style="margin:0; padding-left:18px;">
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
					</div>
				@endif

				<form id="signupForm" method="POST" action="{{ route('rentalsystem.signup.submit') }}">
					@csrf
					<label class="label" for="name">FULL NAME</label>
					<input class="input @error('name') is-invalid @enderror" id="name" name="name" type="text" value="{{ old('name') }}" required>
					@error('name')<div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror

					<label class="label" for="email">EMAIL</label>
					<input class="input @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>
					@error('email')<div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror

					<div class="row-two">
						<div>
							<label class="label" for="password">PASSWORD</label>
							<input class="input @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
							@error('password')<div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror
						</div>
						<div>
							<label class="label" for="password_confirmation">CONFIRM PASSWORD</label>
							<input class="input @error('password_confirmation') is-invalid @enderror" id="password_confirmation" name="password_confirmation" type="password" required>
							@error('password_confirmation')<div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror
						</div>
					</div>

					<label class="label" for="referral_code">REFERRAL CODE (OPTIONAL)</label>
					<input class="input @error('referral_code') is-invalid @enderror" id="referral_code" name="referral_code" type="text" value="{{ old('referral_code') }}" placeholder="Enter referral code if any">
					@error('referral_code')<div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror

					<div class="inline" style="margin:14px 0 16px;">
						<input class="check @error('terms') is-invalid @enderror" id="terms" name="terms" type="checkbox" required>
						<label for="terms">I agree to the <a class="link" href="#">Terms</a> and <a class="link" href="#">Conditions</a></label>
						@error('terms')<div style="color:#b91c1c;font-size:12px;margin-left:8px;">{{ $message }}</div>@enderror
					</div>

					<button class="btn-primary" type="submit" id="signupBtn">
						<span id="btnText">Create account</span>
						<span id="btnSpinner" style="display:none"><i class="fa-solid fa-spinner fa-spin"></i></span>
					</button>
				</form>

				<div class="sep">OR</div>
				<form method="POST" action="{{ route('rentalsystem.google.login') }}" id="googleSignupForm">
					@csrf
					<input type="hidden" name="id_token" id="googleIdToken">
					<button type="button" id="googleSignupBtn" class="btn-google">
						<img src="https://www.gstatic.com/images/branding/product/1x/gsa_64dp.png" width="20" height="20" alt=""> Sign up with Google
					</button>
				</form>
				<p class="small">Already have an account? <a class="link" href="{{ route('rentalsystem.signin') }}">Sign in</a></p>
			</div>
		</section>
	</main>

	<!-- Google Sign-In Script -->
	<script src="https://accounts.google.com/gsi/client" async defer></script>
	<script>
		document.getElementById('signupForm').addEventListener('submit', function(){
			const btn = document.getElementById('signupBtn');
			document.getElementById('btnText').style.display='none';
			document.getElementById('btnSpinner').style.display='inline-block';
			btn.disabled = true;
		});

		// Google Sign-In
		document.getElementById('googleSignupBtn').addEventListener('click', function() {
			google.accounts.id.initialize({
				client_id: '{{ config("services.google.client_id") }}',
				callback: handleCredentialResponse
			});
			
			google.accounts.id.prompt((notification) => {
				if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
					google.accounts.id.renderButton(
						document.getElementById('googleSignupBtn'),
						{ theme: 'outline', size: 'large' }
					);
				}
			});
		});

		function handleCredentialResponse(response) {
			if (response && response.credential) {
				document.getElementById('googleIdToken').value = response.credential;
				document.getElementById('googleSignupForm').submit();
			}
		}
	</script>
</body>
</html>
