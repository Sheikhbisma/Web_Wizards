<?php
$isSignUpActive = isset($_SESSION['signupactive']) && $_SESSION['signupactive'] === true;
$old = $_SESSION['old_input'] ?? [];
?>

<?php echo get_flash() ?>
<?php include __DIR__ . '/../../public/components/header.php'; ?>

<!-- ============================================================
     MARKETLINK AUTH: DIRECT, HIGH-VISIBILITY LOGIN & SIGNUP CARD
     ============================================================ -->
<style>
.ml-auth-page-wrapper {
    background: radial-gradient(circle at 15% 15%, #eef6e6 0%, #f7faf4 50%, #e5eedc 100%);
    min-height: calc(100vh - 120px);
    padding: 50px 15px 80px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.ml-auth-master-card {
    background: #ffffff;
    border-radius: 28px;
    box-shadow: 0 20px 50px rgba(36, 54, 42, 0.12);
    border: 1.5px solid #d4e4c3;
    width: 100%;
    max-width: 1080px;
    overflow: hidden;
}

/* Left Brand Panel */
.ml-auth-side-brand {
    background: linear-gradient(145deg, #283d1c 0%, #3e5c26 55%, #4c702f 100%);
    color: #ffffff;
    padding: 45px 38px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
    min-height: 580px;
    position: relative;
    overflow: hidden;
}

.ml-auth-side-brand::after {
    content: '';
    position: absolute;
    bottom: -60px;
    right: -60px;
    width: 240px;
    height: 240px;
    background: radial-gradient(circle, rgba(160, 188, 121, 0.25) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.ml-brand-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.25);
    color: #ffffff;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.ml-auth-brand-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 2.2rem;
    font-weight: 700;
    line-height: 1.2;
    color: #ffffff;
    margin-top: 20px;
    margin-bottom: 14px;
}

.ml-auth-brand-desc {
    color: #e0edd5;
    font-size: 0.94rem;
    line-height: 1.6;
    margin-bottom: 25px;
}

.ml-feature-list {
    list-style: none;
    padding: 0;
    margin: 0 0 30px;
}

.ml-feature-list li {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #f1f7ec;
    font-size: 0.9rem;
    margin-bottom: 12px;
}

.ml-feature-list li i {
    color: #b5dda0;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.ml-brand-switch-box {
    background: rgba(0, 0, 0, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 18px;
    padding: 16px 20px;
}

.ml-btn-brand-toggle {
    background: #ffffff;
    color: #243815 !important;
    font-weight: 700;
    font-size: 0.88rem;
    padding: 9px 20px;
    border-radius: 50px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.25s ease;
    border: none;
}
.ml-btn-brand-toggle:hover {
    background: #e3ebd8;
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
}

/* Right Form Panel */
.ml-auth-form-panel {
    padding: 40px 38px;
    background: #ffffff;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

/* Tab Switcher */
.ml-auth-tabs {
    display: flex;
    background: #f1f6ec;
    border: 1.5px solid #d8e6cc;
    border-radius: 50px;
    padding: 4px;
    margin-bottom: 24px;
    max-width: 380px;
}

.ml-auth-tab-btn {
    flex: 1;
    text-align: center;
    padding: 9px 16px;
    border-radius: 50px;
    font-size: 0.9rem;
    font-weight: 700;
    color: #556c48;
    background: transparent;
    border: none;
    cursor: pointer;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.ml-auth-tab-btn.active {
    background: #3e5a25;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(62, 90, 37, 0.25);
}

.ml-form-title {
    font-family: 'Fraunces', Georgia, serif;
    font-size: 1.95rem;
    font-weight: 700;
    color: #17240f;
    margin-bottom: 4px;
}

.ml-form-subtitle {
    font-size: 0.88rem;
    color: #5f7453;
    margin-bottom: 22px;
}

/* Clear, High-Contrast Direct Inputs */
.ml-field-group {
    margin-bottom: 14px;
    text-align: left;
}

.ml-field-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 700;
    color: #2b3d1f;
    margin-bottom: 5px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.ml-input-wrap {
    position: relative;
    display: flex;
    align-items: center;
    background: #ffffff;
    border: 1.5px solid #9dc07e;
    border-radius: 14px;
    padding: 0 14px;
    height: 48px;
    transition: all 0.2s ease;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.03);
}

.ml-input-wrap:focus-within {
    border-color: #3e5a25;
    box-shadow: 0 0 0 3.5px rgba(62, 90, 37, 0.16);
    background: #ffffff;
}

.ml-input-icon {
    color: #4a682c;
    font-size: 1rem;
    margin-right: 12px;
    width: 18px;
    text-align: center;
    flex-shrink: 0;
}

.ml-input-field {
    width: 100%;
    border: none;
    background: transparent;
    outline: none;
    font-size: 0.94rem;
    color: #14200c;
    font-weight: 500;
}

.ml-input-field::placeholder {
    color: #798d70;
    font-weight: 400;
    font-size: 0.88rem;
}

.ml-pass-toggle {
    cursor: pointer;
    color: #6a8060;
    padding: 6px;
    font-size: 1rem;
    transition: color 0.2s ease;
    flex-shrink: 0;
}
.ml-pass-toggle:hover {
   color: #1f3012;
   }
   /* "Forgot password?" — styled to sit quietly in the sign-in form.
      Low contrast at rest so it does not compete with the submit button,
      but it is a real link: it darkens and underlines on hover and keeps a
      visible focus ring for keyboard users. */
   .ml-forgot-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.84rem;
      font-weight: 600;
      color: #5c7a3f;
      text-decoration: none;
      transition: color 0.2s ease;
   }
   .ml-forgot-link:hover,
   .ml-forgot-link:focus-visible {
      color: #2b3d1f;
      text-decoration: underline;
   }
   .ml-forgot-link:focus-visible {
      outline: 2px solid #4a5f31;
      outline-offset: 3px;
      border-radius: 4px;
   }

/* Password Hint Box */
.ml-pass-hints {
    background: #f4f9ee;
    border: 1px solid #cce2be;
    border-radius: 12px;
    padding: 10px 14px;
    margin-top: 6px;
    font-size: 0.78rem;
}

.ml-pass-hints p {
    margin: 3px 0;
    line-height: 1.4;
}

/* Big Vibrant Submit Buttons */
.ml-btn-submit {
    width: 100%;
    background: linear-gradient(135deg, #446328 0%, #324c1c 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 14px;
    height: 50px;
    font-weight: 700;
    font-size: 0.94rem;
    letter-spacing: 0.4px;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(50, 76, 28, 0.28);
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 8px;
}
.ml-btn-submit:hover {
    background: linear-gradient(135deg, #2e4419 0%, #1f2f0f 100%);
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(50, 76, 28, 0.36);
}

/* Responsive adjustment */
@media (max-width: 991.98px) {
    .ml-auth-side-brand {
        min-height: auto;
        padding: 30px 24px;
    }
    .ml-auth-form-panel {
        padding: 30px 20px;
    }
}
</style>

<div class="ml-auth-page-wrapper">
    <div class="ml-auth-master-card">
        <div class="row g-0 align-items-stretch">
            
            <!-- ================= LEFT: BRANDING & BENEFITS ================= -->
            <div class="col-lg-5">
                <div class="ml-auth-side-brand">
                    <div>
                        <span class="ml-brand-badge"><i class="fa-solid fa-leaf"></i> MarketLink eGreen</span>
                        <h1 class="ml-auth-brand-title">Farm Fresh Just a Click Away</h1>
                        <p class="ml-auth-brand-desc">
                            Connect directly with verified local farmers, explore weekly market harvest schedules, and pre-order with zero hassle.
                        </p>

                        <ul class="ml-feature-list">
                            <li><i class="fa-solid fa-circle-check"></i> Direct connection with certified local growers</li>
                            <li><i class="fa-solid fa-circle-check"></i> Pre-order and collect fresh at the market stall</li>
                            <li><i class="fa-solid fa-circle-check"></i> Pay directly at pickup &mdash; no online card fees</li>
                            <li><i class="fa-solid fa-circle-check"></i> Live weekly stock availability & transparent pricing</li>
                        </ul>
                    </div>

                    <div class="ml-brand-switch-box d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div id="sideBrandPrompt">
                            <span class="small text-white-50 d-block">Need an account?</span>
                            <span class="small fw-bold text-white">Join our fresh community</span>
                        </div>
                        <button type="button" class="ml-btn-brand-toggle" id="sideBrandToggleBtn" onclick="toggleAuthView()">
                            <span id="sideBrandBtnText">Create Account</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ================= RIGHT: DIRECT FORMS PANEL ================= -->
            <div class="col-lg-7">
                <div class="ml-auth-form-panel">
                    
                    <!-- Tabs Header for Easy Switching -->
                    <div class="ml-auth-tabs">
                        <button type="button" class="ml-auth-tab-btn <?php echo !$isSignUpActive ? 'active' : ''; ?>" id="tabBtnSignIn" onclick="setAuthTab('signin')">
                            <i class="fa-solid fa-right-to-bracket"></i> Sign In
                        </button>
                        <button type="button" class="ml-auth-tab-btn <?php echo $isSignUpActive ? 'active' : ''; ?>" id="tabBtnSignUp" onclick="setAuthTab('signup')">
                            <i class="fa-solid fa-user-plus"></i> Create Account
                        </button>
                    </div>

                    <!-- ================= SIGN IN FORM (DIRECT & CLEAR) ================= -->
                    <div id="sectionSignIn" style="<?php echo $isSignUpActive ? 'display: none;' : 'display: block;'; ?>">
                        <h2 class="ml-form-title">Welcome Back</h2>
                        <p class="ml-form-subtitle">Enter your username or email and password to access your dashboard</p>

<form action="../private/backend-scripting/login-setting.php" method="POST">
<?php echo csrf_field(); ?>

                            <!-- Username / Email Field -->
                            <div class="ml-field-group">
                                <label class="ml-field-label">Username or Email Address</label>
                                <div class="ml-input-wrap">
                                    <i class="fa-solid fa-user ml-input-icon"></i>
                                    <input type="text" name="username" class="ml-input-field" placeholder="Enter your username or email" required autocomplete="username">
                                </div>
                            </div>

                            <!-- Password Field with Show/Hide Eye -->
                            <div class="ml-field-group">
                                <label class="ml-field-label">Password</label>
                                <div class="ml-input-wrap">
                                    <i class="fa-solid fa-lock ml-input-icon"></i>
                                    <input type="password" id="loginPassInput" name="password" class="ml-input-field" placeholder="Enter your password" required autocomplete="current-password">
                                    <span class="ml-pass-toggle" onclick="togglePassEye('loginPassInput', this)" title="Show/Hide Password">
                                        <i class="fa-regular fa-eye-slash"></i>
                                    </span>
                                </div>
                            </div>

                            <!-- Forgot Password: the recovery flow mails a link
                                 that opens the new-password form directly, so it
                                 sits under the password field where someone who
                                 has just failed to sign in actually looks. -->
                            <div class="text-end mt-2">
                                <a href="<?php echo ML_asset('forgot-password'); ?>" class="ml-forgot-link">
                                    <i class="fa-solid fa-key"></i> Forgot password?
                                </a>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" name="login" class="ml-btn-submit mt-3">
                                <span>Sign In to MarketLink</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>

                            <div class="text-center mt-3">
                                <span class="small text-muted"><i class="fa-solid fa-shield-halved text-success me-1"></i> Secured by MarketLink CSRF Protection</span>
                            </div>
                        </form>
                    </div>

                    <!-- ================= SIGN UP FORM (ALL INPUTS DIRECT & SAMNAY) ================= -->
                    <div id="sectionSignUp" style="<?php echo $isSignUpActive ? 'display: block;' : 'display: none;'; ?>">
                        <h2 class="ml-form-title">Create Account</h2>
                        <p class="ml-form-subtitle">Register to reserve fresh harvests or manage your local farm stall</p>

<form action="../private/backend-scripting/signup-setting.php" id="signupform" method="POST" onsubmit="return validateSignUp(event)">
<?php echo csrf_field(); ?>

                            <!-- Full Name -->
                            <div class="ml-field-group">
                                <label class="ml-field-label">Full Name</label>
                                <div class="ml-input-wrap">
                                    <i class="fa-solid fa-user ml-input-icon"></i>
                                    <input type="text" id="fullName" name="name" class="ml-input-field" placeholder="e.g. Ayesha Khan" value="<?php echo sanitize_output($old['name'] ?? '') ?>" required autocomplete="name">
                                </div>
                                <div class="text-danger d-none small mt-1" id="usernameerr">Name must be at least 3 characters</div>
                            </div>

                            <!-- Email Address -->
                            <div class="ml-field-group">
                                <label class="ml-field-label">Email Address</label>
                                <div class="ml-input-wrap">
                                    <i class="fa-solid fa-envelope ml-input-icon"></i>
                                    <input type="email" id="emailAddress" name="email" class="ml-input-field" placeholder="e.g. ayesha@example.com" value="<?php echo sanitize_output($old['email'] ?? '') ?>" required autocomplete="email">
                                </div>
                            </div>

                            <!-- Contact Number -->
                            <div class="ml-field-group">
                                <label class="ml-field-label">Contact Number (Format: 03XXXXXXXXX)</label>
                                <div class="ml-input-wrap">
                                    <i class="fa-solid fa-phone ml-input-icon"></i>
                                    <input type="tel" id="contactNumber" name="contact" class="ml-input-field" placeholder="e.g. 03209087654" value="<?php echo sanitize_output($old['contact'] ?? '') ?>" required autocomplete="tel">
                                </div>
                                <div class="text-danger d-none small mt-1" id="contacterr">Invalid Number format (Must be valid 11-digit mobile: 03XXXXXXXXX)</div>
                            </div>

                            <!-- Password with Show/Hide Eye and Clear Inline Requirements -->
                            <div class="ml-field-group">
                                <label class="ml-field-label">Password</label>
                                <div class="ml-input-wrap">
                                    <i class="fa-solid fa-lock ml-input-icon"></i>
                                    <input type="password" id="passwordInput" name="password" class="ml-input-field" placeholder="Create a secure password" required autocomplete="new-password">
                                    <span class="ml-pass-toggle" onclick="togglePassEye('passwordInput', this)" title="Show/Hide Password">
                                        <i class="fa-regular fa-eye-slash"></i>
                                    </span>
                                </div>
                                
                                <!-- Inline Requirements List (Visible right below password) -->
                                <div class="ml-pass-hints d-none" id="messageBox">
                                    <p class="text-danger small" id="length">&#10008; At least 8 characters</p>
                                    <p class="text-danger small" id="alpha">&#10008; At least one letter (A-Z or a-z)</p>
                                    <p class="text-danger small" id="characters">&#10008; Include a number and a special symbol (@, #, $, etc.)</p>
                                </div>
                                <div class="text-danger d-none small mt-1" id="passerr">Please satisfy password requirements</div>
                            </div>

                            <!-- Confirm Password -->
                            <div class="ml-field-group">
                                <label class="ml-field-label">Confirm Password</label>
                                <div class="ml-input-wrap">
                                    <i class="fa-solid fa-lock ml-input-icon"></i>
                                    <input type="password" id="confirmPassword" name="confirm_password" class="ml-input-field" placeholder="Re-enter your password" required autocomplete="new-password">
                                    <span class="ml-pass-toggle" onclick="togglePassEye('confirmPassword', this)" title="Show/Hide Password">
                                        <i class="fa-regular fa-eye-slash"></i>
                                    </span>
                                </div>
                                <div class="text-danger d-none small mt-1" id="conferr">Passwords do not match</div>
                            </div>

                            <!-- Role Selector -->
                            <div class="ml-field-group">
                                <label class="ml-field-label">Account Role</label>
                                <div class="ml-input-wrap">
                                    <i class="fa-solid fa-user-tag ml-input-icon"></i>
                                    <select id="role" name="role" class="ml-input-field" required>
                                        <option value="" disabled selected>Select Account Role</option>
                                        <option value="customer" <?php echo (($old['role'] ?? '') == 'customer') ? 'selected' : ''; ?>>Customer (Browse & Pre-order Harvest)</option>
                                        <option value="farmer" <?php echo (($old['role'] ?? '') == 'farmer') ? 'selected' : ''; ?>>Farmer (List Produce Stall & Manage Inventory)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Farmer Extra Fields (Seamlessly displayed when Farmer is selected) -->
                            <div id="farmerExtra" class="<?php echo (($old['role'] ?? '') == 'farmer') ? '' : 'd-none'; ?>">
                                <div class="ml-field-group">
                                    <label class="ml-field-label">Stall / Farm Business Name</label>
                                    <div class="ml-input-wrap">
                                        <i class="fa-solid fa-store ml-input-icon"></i>
                                        <input type="text" name="stall_name" id="stallName" class="ml-input-field" placeholder="e.g. Green Acres Organic Farm" value="<?php echo sanitize_output($old['stall_name'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="ml-field-group">
                                    <label class="ml-field-label">Contact Person Name</label>
                                    <div class="ml-input-wrap">
                                        <i class="fa-solid fa-id-badge ml-input-icon"></i>
                                        <input type="text" name="contact_person" id="contactPerson" class="ml-input-field" placeholder="e.g. Muhammad Tariq" value="<?php echo sanitize_output($old['contact_person'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="ml-field-group">
                                    <label class="ml-field-label">Farm / Business Address</label>
                                    <div class="ml-input-wrap">
                                        <i class="fa-solid fa-location-dot ml-input-icon"></i>
                                        <input type="text" name="address" id="farmerAddress" class="ml-input-field" placeholder="e.g. Stall #14, Sunday Organic Market" value="<?php echo sanitize_output($old['address'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" name="register" class="ml-btn-submit mt-3">
                                <span>Create MarketLink Account</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <?php unset($_SESSION['old_input']); ?>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function setAuthTab(tab) {
    const secSignIn = document.getElementById('sectionSignIn');
    const secSignUp = document.getElementById('sectionSignUp');
    const tabSignIn = document.getElementById('tabBtnSignIn');
    const tabSignUp = document.getElementById('tabBtnSignUp');
    const sidePrompt = document.getElementById('sideBrandPrompt');
    const sideBtnText = document.getElementById('sideBrandBtnText');

    if (tab === 'signup') {
        secSignIn.style.display = 'none';
        secSignUp.style.display = 'block';
        tabSignIn.classList.remove('active');
        tabSignUp.classList.add('active');
        if (sidePrompt) sidePrompt.innerHTML = '<span class="small text-white-50 d-block">Already registered?</span><span class="small fw-bold text-white">Sign in to your account</span>';
        if (sideBtnText) sideBtnText.innerText = 'Sign In';
    } else {
        secSignIn.style.display = 'block';
        secSignUp.style.display = 'none';
        tabSignIn.classList.add('active');
        tabSignUp.classList.remove('active');
        if (sidePrompt) sidePrompt.innerHTML = '<span class="small text-white-50 d-block">Need an account?</span><span class="small fw-bold text-white">Join our fresh community</span>';
        if (sideBtnText) sideBtnText.innerText = 'Create Account';
    }
}

function toggleAuthView() {
    const secSignIn = document.getElementById('sectionSignIn');
    if (secSignIn && secSignIn.style.display !== 'none') {
        setAuthTab('signup');
    } else {
        setAuthTab('signin');
    }
}

function togglePassEye(inputId, btnEl) {
    const input = document.getElementById(inputId);
    const icon = btnEl.querySelector('i');
    if (!input || !icon) return;
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    }
}

document.addEventListener("DOMContentLoaded", () => {
    // Password validation live listener
    const userPass = document.getElementById('passwordInput');
    const messageBox = document.getElementById('messageBox');
    const passLength = document.getElementById('length');
    const passAlpha = document.getElementById('alpha');
    const passChars = document.getElementById('characters');

    if (userPass) {
        userPass.addEventListener('focus', () => {
            if (messageBox) messageBox.classList.remove('d-none');
        });

        userPass.addEventListener('input', () => {
            const val = userPass.value;
            if (passLength) {
                passLength.className = val.length >= 8 ? "text-success small" : "text-danger small";
                passLength.innerHTML = val.length >= 8 ? "&#10004; At least 8 characters" : "&#10008; At least 8 characters";
            }
            if (passAlpha) {
                const hasAlpha = /[A-Za-z]/.test(val);
                passAlpha.className = hasAlpha ? "text-success small" : "text-danger small";
                passAlpha.innerHTML = hasAlpha ? "&#10004; At least one letter (A-Z or a-z)" : "&#10008; At least one letter (A-Z or a-z)";
            }
            if (passChars) {
                const hasNumSpecial = /(?=.*\d)(?=.*[!@#$%^&*])/.test(val);
                passChars.className = hasNumSpecial ? "text-success small" : "text-danger small";
                passChars.innerHTML = hasNumSpecial ? "&#10004; Includes number and special character" : "&#10008; Include a number and a special symbol (@, #, $, etc.)";
            }
        });
    }

    // Live confirm-password match check
    const confirmPass = document.getElementById('confirmPassword');
    if (userPass && confirmPass) {
        const confErr = document.getElementById('conferr');
        const checkMatch = function () {
            if (confirmPass.value === '') return;
            if (userPass.value !== confirmPass.value) {
                if (confErr) confErr.classList.remove('d-none');
                confirmPass.setCustomValidity('Passwords do not match');
            } else {
                if (confErr) confErr.classList.add('d-none');
                confirmPass.setCustomValidity('');
            }
        };
        userPass.addEventListener('input', checkMatch);
        confirmPass.addEventListener('input', checkMatch);
    }

    // Role selector listener for farmer fields
    const roleSelect = document.getElementById('role');
    const farmerExtra = document.getElementById('farmerExtra');
    if (roleSelect && farmerExtra) {
        roleSelect.addEventListener('change', function () {
            if (this.value === 'farmer') {
                farmerExtra.classList.remove('d-none');
            } else {
                farmerExtra.classList.add('d-none');
            }
        });
    }
});

function validateSignUp(e) {
    let isValid = true;
    const username = document.getElementById("fullName").value.trim();
    const contact = document.getElementById("contactNumber").value.trim();
    const passwordValue = document.getElementById('passwordInput').value;
    const contactPattern = /^03[012347][0-9]{8}$/;

    if (username.length < 3) {
        const uErr = document.querySelector("#usernameerr");
        if (uErr) uErr.classList.remove("d-none");
        isValid = false;
    } else {
        const uErr = document.querySelector("#usernameerr");
        if (uErr) uErr.classList.add("d-none");
    }

    if (!contactPattern.test(contact)) {
        const cErr = document.querySelector("#contacterr");
        if (cErr) cErr.classList.remove("d-none");
        isValid = false;
    } else {
        const cErr = document.querySelector("#contacterr");
        if (cErr) cErr.classList.add("d-none");
    }

    const isLengthValid = passwordValue.length >= 8;
    const checkAlpha = /[A-Za-z]/.test(passwordValue);
    const checkChars = /(?=.*\d)(?=.*[!@#$%^&*])/.test(passwordValue);

    if (!isLengthValid || !checkAlpha || !checkChars) {
        const pErr = document.querySelector("#passerr");
        if (pErr) pErr.classList.remove("d-none");
        isValid = false;
    } else {
        const pErr = document.querySelector("#passerr");
        if (pErr) pErr.classList.add("d-none");
    }

    const confirmValue = document.getElementById('confirmPassword') ? document.getElementById('confirmPassword').value : '';
    if (passwordValue !== confirmValue) {
        const confErr = document.querySelector("#conferr");
        if (confErr) confErr.classList.remove("d-none");
        isValid = false;
    } else {
        const confErr = document.querySelector("#conferr");
        if (confErr) confErr.classList.add("d-none");
    }

    const roleBox = document.getElementById("role");
    if (roleBox && roleBox.value === "farmer") {
        const stallName = document.getElementById("stallName").value.trim();
        const contactPerson = document.getElementById("contactPerson").value.trim();
        const farmerAddress = document.getElementById("farmerAddress").value.trim();
        if (stallName === "" || contactPerson === "" || farmerAddress === "") {
            alert("Farmers must enter stall name, contact person and business address.");
            isValid = false;
        }
    }

    if (!isValid) {
        e.preventDefault();
    }
    return isValid;
}
</script>

<?php include __DIR__ . '/../../public/components/footer.php'; ?>