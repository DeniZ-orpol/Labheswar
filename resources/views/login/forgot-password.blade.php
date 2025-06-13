<style>
    .reset-form {
        max-width: 400px;
        margin: 50px auto;
        padding: 25px;
        background: #ffffff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        font-family: sans-serif;
    }

    .reset-form h2 {
        text-align: center;
        margin-bottom: 20px;
        color: #333;
    }

    .reset-form input[type="email"] {
        width: 100%;
        padding: 12px 15px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 14px;
    }

    .reset-form button {
        width: 100%;
        padding: 12px;
        background-color: #2D5F72;
        color: white;
        font-weight: bold;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .reset-form button:hover {
        background-color: #0056b3;
    }
</style>
<form method="POST" action="{{ route('password.email') }}" class="reset-form">
    @csrf
    <h2>Reset Password</h2>
    <input type="email" name="email" placeholder="Enter your email" required>
    <button type="submit">Send Password Reset Link</button>
</form>
