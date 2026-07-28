import { auth, database } from './firebase-config.js?v=18';
import {
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    signOut,
    onAuthStateChanged,
    updateProfile,
    sendPasswordResetEmail,
    sendEmailVerification
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js";
import {
    ref, set, get
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";

export function requireAuth(redirectTo = 'login.html') {
    return new Promise(resolve => {
        onAuthStateChanged(auth, user => {
            if (!user) { window.location.href = redirectTo; return; }
            resolve(user);
        });
    });
}

export function redirectIfLoggedIn(redirectTo = 'index.html') {
    onAuthStateChanged(auth, user => {
        if (user) window.location.href = redirectTo;
    });
}

export { auth, onAuthStateChanged, signOut };

// ── Email-verification gate ──────────────────────────────────
// These emails skip verification: the shared guest/demo account and known
// test logins (which use non-real inboxes and can't receive a link).
// Add any other test emails here so they aren't locked out.
export const VERIFY_EXEMPT = ['guest@gmail.com', 'test1@gmail.com', 'debapriyoguha@gmail.com'];

export function needsEmailVerification(user) {
    if (!user || user.emailVerified) return false;
    return !VERIFY_EXEMPT.includes((user.email || '').toLowerCase());
}

export function initLoginPage() {
    redirectIfLoggedIn();

    const loginForm  = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const tabLogin   = document.getElementById('tabLogin');
    const tabSignup  = document.getElementById('tabSignup');
    const errBox     = document.getElementById('authError');

    function showErr(msg)     { errBox.className = 'auth-error';   errBox.textContent = msg; errBox.style.display = 'block'; }
    function showSuccess(msg) { errBox.className = 'auth-success'; errBox.textContent = msg; errBox.style.display = 'block'; }
    function hideErr()        { errBox.style.display = 'none'; }

    // Forgot password
    document.getElementById('forgotToggle')?.addEventListener('click', e => {
        e.preventDefault();
        const wrap = document.getElementById('forgotWrap');
        wrap.style.display = wrap.style.display === 'flex' ? 'none' : 'flex';
        // Pre-fill email if typed
        const emailVal = loginForm.email?.value?.trim();
        if (emailVal) document.getElementById('forgotEmail').value = emailVal;
    });

    document.getElementById('forgotBtn')?.addEventListener('click', async () => {
        const email = document.getElementById('forgotEmail').value.trim();
        if (!email) { showErr('Enter your email first.'); return; }
        try {
            await sendPasswordResetEmail(auth, email);
            showSuccess('✓ Reset link sent! Check your inbox (and spam folder).');
            document.getElementById('forgotWrap').style.display = 'none';
        } catch (err) { showErr(friendlyError(err.code)); }
    });

    tabLogin?.addEventListener('click', () => {
        tabLogin.classList.add('active');
        tabSignup.classList.remove('active');
        loginForm.style.display  = 'block';
        signupForm.style.display = 'none';
        hideErr();
    });

    tabSignup?.addEventListener('click', () => {
        tabSignup.classList.add('active');
        tabLogin.classList.remove('active');
        signupForm.style.display = 'block';
        loginForm.style.display  = 'none';
        hideErr();
    });

    loginForm?.addEventListener('submit', async e => {
        e.preventDefault();
        hideErr();
        try {
            await signInWithEmailAndPassword(auth, loginForm.email.value.trim(), loginForm.password.value);
            window.location.href = 'index.html';
        } catch (err) { showErr(friendlyError(err.code)); }
    });

    // ── Guest / demo login ──────────────────────────────────
    // Anyone can explore the site with this shared account. It self-creates
    // the first time it is used. (Firebase requires a password of >= 6 chars,
    // so the guest password is "guest1234", not "1234".)
    const GUEST_EMAIL = 'guest@gmail.com';
    const GUEST_PASS  = 'guest1234';
    const guestBtn    = document.getElementById('guestBtn');
    guestBtn?.addEventListener('click', async () => {
        hideErr();
        const original = guestBtn.innerHTML;
        guestBtn.disabled = true;
        guestBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Entering…';
        try {
            await signInWithEmailAndPassword(auth, GUEST_EMAIL, GUEST_PASS);
            window.location.href = 'index.html';
            return;
        } catch (err) {
            // First run: create the shared guest account, then it is signed in.
            if (err.code === 'auth/user-not-found' || err.code === 'auth/invalid-credential') {
                try {
                    const cred = await createUserWithEmailAndPassword(auth, GUEST_EMAIL, GUEST_PASS);
                    await updateProfile(cred.user, { displayName: 'Guest' });
                    await set(ref(database, `users/${cred.user.uid}`), {
                        uid:         cred.user.uid,
                        displayName: 'Guest',
                        email:       GUEST_EMAIL,
                        photoURL:    '',
                        bio:         'Demo guest account',
                        postCount:   0,
                        createdAt:   Date.now()
                    });
                    window.location.href = 'index.html';
                    return;
                } catch (e2) { showErr(friendlyError(e2.code)); }
            } else {
                showErr(friendlyError(err.code));
            }
            guestBtn.disabled = false;
            guestBtn.innerHTML = original;
        }
    });

    signupForm?.addEventListener('submit', async e => {
        e.preventDefault();
        hideErr();
        const name  = signupForm.displayName.value.trim();
        const email = signupForm.email.value.trim();
        const pass  = signupForm.password.value;
        const pass2 = signupForm.password2.value;

        if (pass !== pass2)  { showErr('Passwords do not match.'); return; }
        if (pass.length < 6) { showErr('Password must be at least 6 characters.'); return; }
        if (!name)           { showErr('Please enter your name.'); return; }

        try {
            const cred = await createUserWithEmailAndPassword(auth, email, pass);
            await updateProfile(cred.user, { displayName: name });
            await set(ref(database, `users/${cred.user.uid}`), {
                uid:         cred.user.uid,
                displayName: name,
                email:       email,
                photoURL:    '',
                bio:         '',
                postCount:   0,
                createdAt:   Date.now()
            });
            // Send email verification, then send them to the verify gate
            sendEmailVerification(cred.user).catch(() => {});
            window.location.href = 'verify.html';
        } catch (err) { showErr(friendlyError(err.code)); }
    });
}

function friendlyError(code) {
    const map = {
        'auth/user-not-found':       'No account found with this email.',
        'auth/wrong-password':       'Incorrect password.',
        'auth/invalid-credential':   'Incorrect email or password.',
        'auth/email-already-in-use': 'This email is already registered.',
        'auth/invalid-email':        'Please enter a valid email address.',
        'auth/too-many-requests':    'Too many attempts. Please try again later.',
        'auth/weak-password':        'Password is too weak.',
    };
    return map[code] || 'Something went wrong. Please try again.';
}
