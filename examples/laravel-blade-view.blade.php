{{-- resources/views/contact.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form - Laravel SmartCaptcha</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .error {
            color: red;
            font-size: 14px;
            margin-top: 5px;
        }
        .success {
            color: green;
            padding: 10px;
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
        .captcha-container {
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <h1>Contact Form</h1>

    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('contact.submit') }}">
        @csrf

        <div class="form-group">
            <label for="name">Name:</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="{{ old('name') }}" 
                required
            >
            @error('name')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">Email:</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="{{ old('email') }}" 
                required
            >
            @error('email')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="message">Message:</label>
            <textarea 
                id="message" 
                name="message" 
                rows="5" 
                required
            >{{ old('message') }}</textarea>
            @error('message')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <!-- SmartCaptcha Widget -->
        <div class="captcha-container">
            <div id="captcha-container"></div>
            @error('smart-token')
                <div class="error">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit">Send Message</button>
    </form>

    <!-- Load SmartCaptcha script -->
    <script src="https://smartcaptcha.yandexcloud.net/captcha.js?render=onload&onload=onloadFunction" defer></script>
    
    <script>
        function onloadFunction() {
            if (window.smartCaptcha) {
                const container = document.getElementById('captcha-container');
                
                // Render captcha widget
                const widgetId = window.smartCaptcha.render(container, {
                    sitekey: '{{ $clientKey }}', // Client key from config
                    hl: '{{ app()->getLocale() }}', // Use Laravel locale
                    callback: function(token) {
                        console.log('Captcha passed!');
                    },
                    'error-callback': function() {
                        console.error('Captcha error');
                        alert('Captcha error occurred. Please try again.');
                    },
                    'expired-callback': function() {
                        console.warn('Captcha expired');
                        alert('Captcha expired. Please verify again.');
                    }
                });
            }
        }
    </script>
</body>
</html>
