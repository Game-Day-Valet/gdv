<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Sign In - Game Day Valet</title>
	<meta name="csrf-token" content="{{ csrf_token() }}">
	<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <!-- App favicon -->
    <link rel="shortcut icon" href="/images/logo-sm.png">
	<style>
		:root {
			--primary: #c94c4c;
			--ink: #0f172a;
			--muted: #64748b;
			--border: #e2e8f0;
			--bg: #ffffff;
		}
		* { box-sizing: border-box; }
		body { margin: 0; background: var(--bg); color: var(--ink); font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif; }

		/* Header (website-style) */
		.navbar { border-bottom: 1px solid var(--border); background:#fff; }
		.navbar .inner { max-width: 1200px; margin: 0 auto; padding: 14px 20px; display: flex; align-items: center; justify-content: space-between; }
		.brand { font-weight: 800; letter-spacing: -.3px; color: var(--ink); text-decoration: none; font-size: 18px; }
		.nav { display: flex; gap: 18px; }
		.nav a { color: var(--muted); text-decoration: none; font-weight: 600; }
		.nav a:hover { color: var(--ink); }

		/* Page layout */
		.page { max-width: 1200px; margin: 0 auto; padding: 48px 20px; display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 56px; align-items: center; }
		@media (max-width: 960px) { .page { grid-template-columns: 1fr; padding: 28px 16px; gap: 28px; } }

		.hero h1 { font-size: 42px; line-height: 1.1; margin: 0 0 12px; font-weight: 900; letter-spacing: -0.6px; }
		.hero p { color: var(--muted); font-size: 16px; max-width: 580px; }
		.hero-illustration { margin-top: 24px; border: 1px dashed var(--border); border-radius: 16px; padding: 28px; color: var(--muted); }
        .hero-illustration img { border-radius: 16px; height: 300px; object-fit: cover; }
		/* Form card */
		.card { width: 100%; max-width: 520px; border: 1px solid var(--border); border-radius: 16px; background: #fff; box-shadow: 0 8px 32px rgba(2,8,23,.06); padding: 24px; }
		.card h2 { margin: 0 0 6px; font-size: 24px; font-weight: 800; }
		.card .sub { margin: 0 0 18px; color: var(--muted); }
		.label { display: block; font-size: 12px; letter-spacing: .14em; color: #94a3b8; margin: 12px 0 8px; font-weight: 800; }
		.input { width: 100%; border: 1.6px solid var(--border); border-radius: 12px; padding: 12px 14px; font-size: 15px; outline: none; transition: border-color .2s, box-shadow .2s; }
		.input:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgb(201 76 76 / 12%); }
		.input-wrap { position: relative; }
		.toggle-pass { position:absolute; right:10px; top:50%; transform:translateY(-50%); border:0; background:transparent; color:#64748b; cursor:pointer; padding:6px; }
		.toggle-pass:hover { color:#0f172a; }
		/* Hide browser-native password reveal/clear controls */
		input[type="password"]::-ms-reveal,
		input[type="password"]::-ms-clear { display:none; }
		.input::-ms-reveal, .input::-ms-clear { display:none; }
		.row-between { display: flex; align-items: center; justify-content: space-between; margin: 10px 0 16px; }
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
			<h1>Sign in to continue</h1>
			<p>Access your rentals, browse tournaments and manage your team from a fast, modern web dashboard.</p>
			<div class="hero-illustration">
                <img src="{{ asset('images/main-thumbnail1.jpg') }}" width="100%" alt="">
            </div>
		</section>

		<section>
			<div class="card">
				<h2>Welcome back</h2>
				<p class="sub">Enter your credentials to sign in</p>

				@if(session('success'))
					<div style="background:#ecfdf5;border:1px solid #10b98133;color:#065f46;padding:10px 12px;border-radius:10px;margin-bottom:10px;">{{ session('success') }}</div>
				@endif
				@if(session('error'))
					<div style="background:#fef2f2;border:1px solid #ef444433;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:10px;">{{ session('error') }}</div>
				@endif
				@if($errors->has('google'))
					<div style="background:#fef2f2;border:1px solid #ef444433;color:#991b1b;padding:10px 12px;border-radius:10px;margin-bottom:10px;">{{ $errors->first('google') }}</div>
				@endif

				<form id="signinForm" method="POST" action="{{ route('rentalsystem.signin.submit') }}">
					@csrf
					<label class="label" for="email">EMAIL</label>
					<input class="input @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required>
					@error('email')<div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror

					<label class="label" for="password">PASSWORD</label>
					<div class="input-wrap">
						<input class="input @error('password') is-invalid @enderror" id="password" name="password" type="password" required>
						<button type="button" class="toggle-pass" data-target="password" aria-label="Toggle password visibility"><i class="fa-regular fa-eye"></i></button>
					</div>
					@error('password')<div style="color:#b91c1c;font-size:12px;margin-top:6px;">{{ $message }}</div>@enderror

					<div class="row-between">
						<label style="display:flex;align-items:center;gap:10px;">
							<input class="check" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
							<span style="font-size:14px;">Remember me</span>
						</label>
						<a class="link" href="{{ route('rentalsystem.forgot-password') }}">Forgot password</a>
					</div>

					<p class="small" style="margin-top:10px;">By continuing, you agree to our <a class="link" href="{{ route('rentalsystem.privacy-policy') }}" target="_blank">Privacy Policy</a> and <a class="link" href="{{ url('/terms') }}" target="_blank">Terms & Conditions</a>.</p>

					<button class="btn-primary" id="signinBtn" type="submit">
						<span id="btnText">Sign in</span>
						<span id="btnSpinner" style="display:none"><i class="fa-solid fa-spinner fa-spin"></i></span>
					</button>
				</form>

				<div class="sep">OR</div>
				<form method="POST" action="{{ route('rentalsystem.google.login') }}" id="googleLoginForm">
					@csrf
					<input type="hidden" name="id_token" id="googleIdToken">
					<button type="button" id="googleSignInBtn" class="btn-google">
						<img src="https://www.gstatic.com/images/branding/product/1x/gsa_64dp.png" width="20" height="20" alt=""> Continue with Google
					</button>
				</form>
				<p class="small">Don't have an account? <a class="link" href="{{ route('rentalsystem.signup') }}">Create account</a></p>
			</div>
		</section>
	</main>

	<!-- Google Sign-In Script -->
	<script src="https://accounts.google.com/gsi/client" async defer></script>
	<script>
		document.getElementById('signinForm').addEventListener('submit', function(){
			const btn = document.getElementById('signinBtn');
			document.getElementById('btnText').style.display='none';
			document.getElementById('btnSpinner').style.display='inline-block';
			btn.disabled = true;
		});

		// Google Sign-In
		document.getElementById('googleSignInBtn').addEventListener('click', function() {
			google.accounts.id.initialize({
				client_id: '{{ config("services.google.client_id") }}',
				callback: handleCredentialResponse
			});
			
			google.accounts.id.prompt((notification) => {
				if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
					google.accounts.id.renderButton(
						document.getElementById('googleSignInBtn'),
						{ theme: 'outline', size: 'large' }
					);
				}
			});
		});

		function handleCredentialResponse(response) {
			if (response && response.credential) {
				document.getElementById('googleIdToken').value = response.credential;
				document.getElementById('googleLoginForm').submit();
			}
		}

		// Always-visible password toggle
		document.querySelectorAll('.toggle-pass').forEach(function(btn){
			btn.addEventListener('click', function(){
				const targetId = btn.getAttribute('data-target');
				const input = document.getElementById(targetId);
				if(!input) return;
				const isPassword = input.getAttribute('type') === 'password';
				input.setAttribute('type', isPassword ? 'text' : 'password');
				btn.innerHTML = isPassword ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
			});
		});
	</script>
</body>
</html>
