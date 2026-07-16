import { auth, db } from './firebase-config.js';
import {
    createUserWithEmailAndPassword,
    signInWithEmailAndPassword,
    signOut,
    onAuthStateChanged,
    updateProfile
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js";
import {
    doc, setDoc, getDoc, serverTimestamp
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-firestore.js";

// ── Auth state guard ────────────────────────────────────────
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

// ── Login page logic ────────────────────────────────────────
export function initLoginPage() {
    redirectIfLoggedIn();

    const loginForm  = document.getElementById('loginForm');
    const signupForm = document.getElementById('signupForm');
    const tabLogin   = document.getElementById('tabLogin');
    const tabSignup  = document.getElementById('tabSignup');
    const errBox     = document.getElementById('authError');

    function showErr(msg) {
        errBox.textContent = msg;
        errBox.style.display = 'block';
    }
    function hideErr() { errBox.style.display = 'none'; }

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
        const email = loginForm.email.value.trim();
        const pass  = loginForm.password.value;
        try {
            await signInWithEmailAndPassword(auth, email, pass);
            window.location.href = 'index.html';
        } catch (err) {
            showErr(friendlyError(err.code));
        }
    });

    signupForm?.addEventListener('submit', async e => {
        e.preventDefault();
        hideErr();
        const name  = signupForm.displayName.value.trim();
        const email = signupForm.email.value.trim();
        const pass  = signupForm.password.value;
        const pass2 = signupForm.password2.value;

        if (pass !== pass2) { showErr('Passwords do not match.'); return; }
        if (pass.length < 6) { showErr('Password must be at least 6 characters.'); return; }
        if (!name) { showErr('Please enter your name.'); return; }

        try {
            const cred = await createUserWithEmailAndPassword(auth, email, pass);
            await updateProfile(cred.user, { displayName: name });
            await setDoc(doc(db, 'users', cred.user.uid), {
                uid:          cred.user.uid,
                displayName:  name,
                email:        email,
                photoURL:     '',
                bio:          '',
                postCount:    0,
                createdAt:    serverTimestamp()
            });
            window.location.href = 'index.html';
        } catch (err) {
            showErr(friendlyError(err.code));
        }
    });
}

function friendlyError(code) {
    const map = {
        'auth/user-not-found':    'No account found with this email.',
        'auth/wrong-password':    'Incorrect password.',
        'auth/email-already-in-use': 'This email is already registered.',
        'auth/invalid-email':     'Please enter a valid email address.',
        'auth/too-many-requests': 'Too many attempts. Please try again later.',
        'auth/weak-password':     'Password is too weak.',
    };
    return map[code] || 'Something went wrong. Please try again.';
}
