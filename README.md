# ✦ knackbook

A small talent-sharing social feed, users sign up, post their talent (text, image, or
a video link), and others can **like, comment, and rate (1 to 10)** each post. Built as a
static site on **GitHub Pages** with **Firebase** as the backend.

**Live:** https://debapriyoguha.github.io/Talent_Verse/

> The GitHub repo is named `Talent_Verse`, but the product/brand shown on the site is
> **knackbook**. (Only the URL keeps the old name.)

---

## 👀 Quick look (Guest access)

Don't want to sign up? On the login page click **“Continue as Guest”**, or log in with:

```
Email:    guest@gmail.com
Password: guest1234
```

The guest account is shared and self-creates the first time it's used. (Firebase requires
a password of at least 6 characters, so it's `guest1234`, not `1234`.)

---

## 🧱 Tech stack

| Layer | What it uses |
|-------|--------------|
| Hosting | GitHub Pages (static files) |
| UI | Plain HTML + CSS + vanilla JS (ES modules), Font Awesome icons |
| Auth | Firebase Authentication (email/password) |
| Database | Firebase **Realtime Database** (region: `asia-southeast1`) |
| Media | Images are compressed in-browser to base64 and stored in the post; videos are just YouTube/TikTok/Vimeo links |

There is **no server**. All logic runs in the browser and talks straight to Firebase.

> ℹ️ The many `.php` files (`login.php`, `signup.php`, `messages.php`, …) are from an
> earlier PHP version and are **not used** by the live GitHub Pages site (GitHub Pages
> does not run PHP). The live app is the `.html` + `js/` code only.

---

## 📂 Project structure (what actually runs)

```
index.html          Home feed, create a post + realtime timeline
profile.html        A user's profile + their posts (?uid=<id> for others)
login.html          Login / Sign up / Guest access

js/firebase-config.js   Firebase project keys + init (auth, database)
js/auth.js              Login, signup, guest login, password reset
js/feed.js              Core logic: posts, likes, comments, ratings, feed rendering
css/style.css           All styling (light + dark theme)

database.rules.json     Realtime Database security rules
firebase.json           Firebase project config

migrate.html        One-time tool: copy old Firestore posts → Realtime Database
seed.html           One-time tool: add sample/demo posts (dated ~4 years back)
```

---

## 🗄️ Where is the database?

Firebase **Realtime Database** in project **`knackbook-talentverse`**.

- **Console:** https://console.firebase.google.com/project/knackbook-talentverse/database
- **Data URL:** `https://knackbook-talentverse-default-rtdb.asia-southeast1.firebasedatabase.app`

Data shape:

```
posts/
  <postId>/
    uid, displayName, photoURL
    body, imageURL, videoURL, tag
    likeCount, commentCount, createdAt
    likes/    { <uid>: true }
    ratings/  { <uid>: 1..10 }   ratingAvg, ratingCount
    comments/ { <commentId>: { uid, displayName, body, createdAt } }

users/
  <uid>/  { uid, displayName, email, photoURL, bio, postCount, createdAt }
```

> **Note:** an earlier version of this app used Cloud **Firestore**. It was later switched
> to the **Realtime Database**, so posts made before that switch live in Firestore and are
> recovered with `migrate.html`.

---

## 🔄 How it works

- **Realtime feed.** The home feed uses a Firebase `onValue` listener on `posts`, so any
  change (new post, like, rating, comment) shows up **instantly for everyone**, no manual
  refresh needed.
- **Sidebar widgets** (“Featured” / “Trending”) load **once per page load** (not realtime),
  so those update on reload.
- **Ratings.** Click a star (1 to 10) to rate. Click your current rating again, or the small
  **“clear”** button, to remove your rating. Averages recompute live.
- **Images.** Compressed to base64 in the browser before saving (keeps things free, no
  Firebase Storage needed).

---

## ✉️ Email verification gate

New sign-ups must **verify their email** before they can use the site: after signing up
Firebase emails a verification link, and the user is held on `verify.html` (with *resend*
and *I've verified* buttons) until they confirm. The guest/demo account and listed test
logins are exempt, see `VERIFY_EXEMPT` in `js/auth.js` (add any other test emails there).

> The link is sent from Firebase's default `noreply@…firebaseapp.com` address. Sending it
> from a custom Gmail address would need custom SMTP on the Firebase Blaze plan.

## 🔐 Security rules (summary)

See `database.rules.json`. In short:

- Anyone **logged in** can read all posts and user profiles.
- You can only **edit/delete your own** post; anyone logged in can create a post.
- You can only write **your own** like / rating (keyed by your uid).
- **Ultimate admin** (`debapriyoguha@gmail.com`) can edit/delete **any** post and has full
  database access. Admins are listed in `ADMIN_EMAILS` (`js/auth.js`) for the UI, and the
  matching `auth.token.email` rule in `database.rules.json` for the backend.

---

## 🛠️ Running it yourself

It's static, just serve the folder (or open on GitHub Pages). To point it at your own
Firebase project, edit `js/firebase-config.js` with your project's keys and paste
`database.rules.json` into **Firebase Console → Realtime Database → Rules**.

The `?v=NN` at the end of the `js/*.js` imports is a **cache-buster**, bump the number
(e.g. `?v=15` → `?v=16`) after changing a JS file so browsers fetch the new version.

---

## 🧰 Admin / one-time tools

Open these while logged in:

- **`migrate.html`**, copies old Firestore posts into the Realtime Database (idempotent, 
  safe to re-run, skips already-copied posts).
- **`seed.html`**, inserts a few dated sample posts, including the *“first post from the
  knackbook team”* (idempotent).

You can delete these two files once you're done with them.
