// ── PASTE YOUR FIREBASE CONFIG HERE ──────────────────────────
// From: Firebase Console → Project Settings → Your Apps → Web App → Config
// Replace EVERY value below with your actual values.

import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getAuth }       from "https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js";
import { getFirestore }  from "https://www.gstatic.com/firebasejs/10.12.2/firebase-firestore.js";
import { getStorage }    from "https://www.gstatic.com/firebasejs/10.12.2/firebase-storage.js";

const firebaseConfig = {
    apiKey:            "PASTE_YOUR_API_KEY_HERE",
    authDomain:        "PASTE_YOUR_AUTH_DOMAIN_HERE",
    projectId:         "PASTE_YOUR_PROJECT_ID_HERE",
    storageBucket:     "PASTE_YOUR_STORAGE_BUCKET_HERE",
    messagingSenderId: "PASTE_YOUR_SENDER_ID_HERE",
    appId:             "PASTE_YOUR_APP_ID_HERE"
};

const app     = initializeApp(firebaseConfig);
const auth    = getAuth(app);
const db      = getFirestore(app);
const storage = getStorage(app);

export { auth, db, storage };
