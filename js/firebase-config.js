import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.2/firebase-app.js";
import { getAuth }       from "https://www.gstatic.com/firebasejs/10.12.2/firebase-auth.js";
import { getDatabase }   from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";
import { getStorage }    from "https://www.gstatic.com/firebasejs/10.12.2/firebase-storage.js";

const firebaseConfig = {
    apiKey:            "AIzaSyCj17PctEiymsd57dfovaE1Y0CclzRQFXU",
    authDomain:        "knackbook-talentverse.firebaseapp.com",
    databaseURL:       "https://knackbook-talentverse-default-rtdb.asia-southeast1.firebasedatabase.app",
    projectId:         "knackbook-talentverse",
    storageBucket:     "knackbook-talentverse.firebasestorage.app",
    messagingSenderId: "727243409887",
    appId:             "1:727243409887:web:fde7893c67a85295c41c6a",
    measurementId:     "G-LW09LFL1ED"
};

const app      = initializeApp(firebaseConfig);
const auth     = getAuth(app);
const database = getDatabase(app);
const storage  = getStorage(app);

export { auth, database, storage };
