import { auth, db, storage } from './firebase-config.js';
import {
    collection, addDoc, getDocs, query, where,
    orderBy, serverTimestamp, doc, updateDoc,
    arrayUnion, arrayRemove, getDoc, increment,
    onSnapshot, deleteDoc
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-firestore.js";
import {
    ref, uploadBytes, getDownloadURL
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-storage.js";

export const CATEGORIES = {
    all:         { label: 'All',          icon: 'fas fa-th',            cls: '' },
    music:       { label: 'Music',        icon: 'fas fa-music',          cls: 'tag-music' },
    dance:       { label: 'Dance',        icon: 'fas fa-person-dancing', cls: 'tag-dance' },
    art:         { label: 'Art & Drawing',icon: 'fas fa-palette',        cls: 'tag-art' },
    writing:     { label: 'Writing',      icon: 'fas fa-pen-nib',        cls: 'tag-writing' },
    photography: { label: 'Photography',  icon: 'fas fa-camera',         cls: 'tag-photography' },
    video:       { label: 'Video',        icon: 'fas fa-video',          cls: 'tag-video' },
    acting:      { label: 'Acting',       icon: 'fas fa-masks-theater',  cls: 'tag-acting' },
    cooking:     { label: 'Cooking',      icon: 'fas fa-utensils',       cls: 'tag-cooking' },
    fashion:     { label: 'Fashion',      icon: 'fas fa-shirt',          cls: 'tag-fashion' },
    sports:      { label: 'Sports',       icon: 'fas fa-trophy',         cls: 'tag-sports' },
    gaming:      { label: 'Gaming',       icon: 'fas fa-gamepad',        cls: 'tag-gaming' },
    spoken:      { label: 'Spoken Word',  icon: 'fas fa-microphone',     cls: 'tag-spoken' },
    other:       { label: 'Other',        icon: 'fas fa-star',           cls: 'tag-other' }
};

// ── Submit a post ────────────────────────────────────────────
export async function submitPost(user, text, tag, imageFile) {
    let imageURL = '';
    if (imageFile) {
        const storRef = ref(storage, `posts/${user.uid}_${Date.now()}_${imageFile.name}`);
        const snap    = await uploadBytes(storRef, imageFile);
        imageURL      = await getDownloadURL(snap.ref);
    }

    const userDoc  = await getDoc(doc(db, 'users', user.uid));
    const userData = userDoc.exists() ? userDoc.data() : {};

    await addDoc(collection(db, 'posts'), {
        uid:         user.uid,
        displayName: user.displayName || userData.displayName || 'Anonymous',
        photoURL:    user.photoURL    || userData.photoURL    || '',
        body:        text.trim(),
        imageURL,
        tag,
        likes:       0,
        likedBy:     [],
        commentCount:0,
        createdAt:   serverTimestamp()
    });

    // bump postCount
    await updateDoc(doc(db, 'users', user.uid), { postCount: increment(1) });
}

// ── Load posts (realtime) ────────────────────────────────────
export function loadFeed(container, activeTag, currentUser) {
    container.innerHTML = '<div class="feed-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

    let q;
    if (activeTag && activeTag !== 'all') {
        q = query(
            collection(db, 'posts'),
            where('tag', '==', activeTag),
            orderBy('createdAt', 'desc')
        );
    } else {
        q = query(collection(db, 'posts'), orderBy('createdAt', 'desc'));
    }

    return onSnapshot(q, snapshot => {
        if (snapshot.empty) {
            container.innerHTML = `
            <div class="empty-feed">
                <i class="fas fa-star"></i>
                <p>No posts yet. Be the first to share your talent!</p>
            </div>`;
            return;
        }
        container.innerHTML = '';
        snapshot.forEach(docSnap => {
            const post = { id: docSnap.id, ...docSnap.data() };
            container.appendChild(buildPostCard(post, currentUser));
        });
    });
}

// ── Build a post card ────────────────────────────────────────
function buildPostCard(post, currentUser) {
    const cat      = CATEGORIES[post.tag] || CATEGORIES.other;
    const liked    = currentUser && (post.likedBy || []).includes(currentUser.uid);
    const timeAgo  = formatTime(post.createdAt?.toDate ? post.createdAt.toDate() : new Date());
    const avatar   = post.photoURL || `https://ui-avatars.com/api/?name=${encodeURIComponent(post.displayName)}&background=7c3aed&color=fff`;
    const isOwner  = currentUser && currentUser.uid === post.uid;

    const card = document.createElement('div');
    card.className = 'post-card';
    card.dataset.id = post.id;

    card.innerHTML = `
    <div class="post-header">
        <img src="${avatar}" class="post-avatar" alt="">
        <div class="post-meta">
            <div class="post-author">
                <a href="profile.html?uid=${post.uid}">${escHtml(post.displayName)}</a>
            </div>
            <div class="post-time">${timeAgo}</div>
        </div>
        <a href="index.html?tag=${post.tag}" class="post-tag-badge ${cat.cls}">
            <i class="${cat.icon}"></i> ${cat.label}
        </a>
        ${isOwner ? `<button class="post-delete" data-id="${post.id}" title="Delete post"><i class="fas fa-trash"></i></button>` : ''}
    </div>
    ${post.body ? `<div class="post-body">${escHtml(post.body)}</div>` : ''}
    ${post.imageURL ? `<img src="${post.imageURL}" class="post-image" alt="" loading="lazy">` : ''}
    <div class="post-footer">
        <button class="post-action like-btn ${liked ? 'liked' : ''}" data-id="${post.id}">
            <i class="${liked ? 'fas' : 'far'} fa-heart"></i>
            <span class="like-count">${post.likes || 0}</span>
        </button>
        <button class="post-action comment-toggle" data-id="${post.id}">
            <i class="far fa-comment"></i>
            <span class="comment-count">${post.commentCount || 0}</span>
        </button>
    </div>
    <div class="post-comments" id="comments_${post.id}">
        <div class="comment-list" id="clist_${post.id}"></div>
        <form class="comment-form" data-id="${post.id}">
            <img src="${currentUser ? (currentUser.photoURL || `https://ui-avatars.com/api/?name=${encodeURIComponent(currentUser.displayName)}&background=7c3aed&color=fff`) : ''}" class="post-avatar" alt="" style="width:30px;height:30px">
            <input type="text" placeholder="Write a comment..." class="comment-input" required>
            <button type="submit" class="btn-comment"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>`;

    // Like handler
    card.querySelector('.like-btn')?.addEventListener('click', () => toggleLike(post.id, currentUser));

    // Comment toggle
    card.querySelector('.comment-toggle')?.addEventListener('click', () => {
        const box = document.getElementById(`comments_${post.id}`);
        const open = box.classList.toggle('open');
        if (open) loadComments(post.id, document.getElementById(`clist_${post.id}`));
    });

    // Comment submit
    card.querySelector('.comment-form')?.addEventListener('submit', async e => {
        e.preventDefault();
        const input = e.target.querySelector('.comment-input');
        const text  = input.value.trim();
        if (!text || !currentUser) return;
        input.value = '';
        await postComment(post.id, currentUser, text);
        loadComments(post.id, document.getElementById(`clist_${post.id}`));
    });

    // Delete handler
    card.querySelector('.post-delete')?.addEventListener('click', async () => {
        if (!confirm('Delete this post?')) return;
        await deleteDoc(doc(db, 'posts', post.id));
        card.remove();
    });

    return card;
}

// ── Like / unlike ────────────────────────────────────────────
async function toggleLike(postId, user) {
    if (!user) return;
    const ref    = doc(db, 'posts', postId);
    const snap   = await getDoc(ref);
    const data   = snap.data();
    const liked  = (data.likedBy || []).includes(user.uid);
    await updateDoc(ref, {
        likes:   increment(liked ? -1 : 1),
        likedBy: liked ? arrayRemove(user.uid) : arrayUnion(user.uid)
    });
}

// ── Comments ─────────────────────────────────────────────────
async function postComment(postId, user, text) {
    await addDoc(collection(db, 'posts', postId, 'comments'), {
        uid:         user.uid,
        displayName: user.displayName || 'Anonymous',
        photoURL:    user.photoURL || '',
        body:        text,
        createdAt:   serverTimestamp()
    });
    await updateDoc(doc(db, 'posts', postId), { commentCount: increment(1) });
}

async function loadComments(postId, container) {
    const q    = query(collection(db, 'posts', postId, 'comments'), orderBy('createdAt', 'asc'));
    const snap = await getDocs(q);
    container.innerHTML = '';
    snap.forEach(d => {
        const c = d.data();
        const av = c.photoURL || `https://ui-avatars.com/api/?name=${encodeURIComponent(c.displayName)}&background=7c3aed&color=fff`;
        const div = document.createElement('div');
        div.className = 'comment-item';
        div.innerHTML = `
        <img src="${av}" class="post-avatar" alt="" style="width:28px;height:28px">
        <div class="comment-bubble">
            <span class="comment-author">${escHtml(c.displayName)}</span>
            <span class="comment-text">${escHtml(c.body)}</span>
        </div>`;
        container.appendChild(div);
    });
}

// ── Trending counts ──────────────────────────────────────────
export async function loadTrending(container) {
    const tags = Object.keys(CATEGORIES).filter(k => k !== 'all');
    const counts = {};
    for (const tag of tags) {
        const q    = query(collection(db, 'posts'), where('tag', '==', tag));
        const snap = await getDocs(q);
        counts[tag] = snap.size;
    }
    // Sort by count
    const sorted = tags.filter(t => counts[t] > 0).sort((a,b) => counts[b]-counts[a]).slice(0,8);
    container.innerHTML = '';
    sorted.forEach(tag => {
        const cat = CATEGORIES[tag];
        const n   = counts[tag];
        container.innerHTML += `
        <a href="index.html?tag=${tag}" class="trending-tag">
            <div class="tt-icon ${cat.cls}"><i class="${cat.icon}" style="color:#fff;font-size:.8rem"></i></div>
            <div class="tt-info">
                <div class="tt-name">${cat.label}</div>
                <div class="tt-count">${n} post${n!==1?'s':''}</div>
            </div>
        </a>`;
    });
    if (!sorted.length) container.innerHTML = '<p style="color:var(--muted);font-size:.8rem;padding:8px 0">No posts yet.</p>';
}

// ── Helpers ───────────────────────────────────────────────────
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatTime(date) {
    const s = Math.floor((Date.now() - date.getTime()) / 1000);
    if (s < 60)        return 'Just now';
    if (s < 3600)      return Math.floor(s/60) + ' min ago';
    if (s < 86400)     return Math.floor(s/3600) + 'h ago';
    if (s < 2592000)   return Math.floor(s/86400) + 'd ago';
    if (s < 31536000)  return Math.floor(s/2592000) + 'mo ago';
    return Math.floor(s/31536000) + 'y ago';
}
