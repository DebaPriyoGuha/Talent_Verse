import { database, storage } from './firebase-config.js';
import {
    ref as dbRef, set, push, get, remove, onValue
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-database.js";
import {
    ref as storageRef, uploadBytes, getDownloadURL
} from "https://www.gstatic.com/firebasejs/10.12.2/firebase-storage.js";

export const CATEGORIES = {
    all:         { label: 'All',           icon: 'fas fa-th',             cls: '' },
    music:       { label: 'Music',         icon: 'fas fa-music',          cls: 'tag-music' },
    dance:       { label: 'Dance',         icon: 'fas fa-person-dancing', cls: 'tag-dance' },
    art:         { label: 'Art & Drawing', icon: 'fas fa-palette',        cls: 'tag-art' },
    writing:     { label: 'Writing',       icon: 'fas fa-pen-nib',        cls: 'tag-writing' },
    photography: { label: 'Photography',   icon: 'fas fa-camera',         cls: 'tag-photography' },
    video:       { label: 'Video',         icon: 'fas fa-video',          cls: 'tag-video' },
    acting:      { label: 'Acting',        icon: 'fas fa-masks-theater',  cls: 'tag-acting' },
    cooking:     { label: 'Cooking',       icon: 'fas fa-utensils',       cls: 'tag-cooking' },
    fashion:     { label: 'Fashion',       icon: 'fas fa-shirt',          cls: 'tag-fashion' },
    sports:      { label: 'Sports',        icon: 'fas fa-trophy',         cls: 'tag-sports' },
    gaming:      { label: 'Gaming',        icon: 'fas fa-gamepad',        cls: 'tag-gaming' },
    spoken:      { label: 'Spoken Word',   icon: 'fas fa-microphone',     cls: 'tag-spoken' },
    other:       { label: 'Other',         icon: 'fas fa-star',           cls: 'tag-other' }
};

// ── Submit a post ─────────────────────────────────────────────
export async function submitPost(user, text, tag, imageFile, videoURL = '') {
    let imageURL = '';

    if (imageFile) {
        try {
            imageURL = await compressImage(imageFile, 500, 0.5);
            // If still too large, compress harder
            if (imageURL.length > 400000) {
                imageURL = await compressImage(imageFile, 300, 0.35);
            }
        } catch (e) {
            throw new Error('Image compress failed: ' + e.message);
        }
    }

    // Use Auth profile directly — no blocking DB reads
    await push(dbRef(database, 'posts'), {
        uid:          user.uid,
        displayName:  user.displayName || 'Anonymous',
        photoURL:     user.photoURL    || '',
        body:         text.trim(),
        imageURL,
        videoURL:     videoURL || '',
        tag,
        likeCount:    0,
        commentCount: 0,
        createdAt:    Date.now()
    });
}

// ── Realtime feed ─────────────────────────────────────────────
export function loadFeed(container, activeTag, currentUser) {
    container.innerHTML = '<div class="feed-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</div>';

    // Direct ref — no orderByChild (avoids index requirement); sort client-side
    return onValue(dbRef(database, 'posts'), snapshot => {
        const posts = [];
        snapshot.forEach(child => posts.push({ id: child.key, ...child.val() }));
        posts.sort((a, b) => (b.createdAt || 0) - (a.createdAt || 0));

        const filtered = (!activeTag || activeTag === 'all')
            ? posts
            : posts.filter(p => p.tag === activeTag);

        container.innerHTML = '';
        if (!filtered.length) {
            container.innerHTML = `
            <div class="empty-feed">
                <i class="fas fa-star"></i>
                <p>No posts yet. Be the first to share your talent!</p>
            </div>`;
            return;
        }
        filtered.forEach(post => container.appendChild(buildPostCard(post, currentUser)));
    }, err => {
        container.innerHTML = `<div class="empty-feed"><p>Could not load posts: ${err.message}</p></div>`;
    });
}

// ── Build post card ───────────────────────────────────────────
function buildPostCard(post, currentUser) {
    const cat      = CATEGORIES[post.tag] || CATEGORIES.other;
    const likesObj = post.likes || {};
    const liked    = currentUser && !!likesObj[currentUser.uid];
    const timeAgo  = formatTime(post.createdAt || Date.now());
    const avatar   = post.photoURL
        || `https://ui-avatars.com/api/?name=${encodeURIComponent(post.displayName)}&background=7c3aed&color=fff`;
    const isOwner  = currentUser && currentUser.uid === post.uid;
    const myAvatar = currentUser
        ? (currentUser.photoURL || `https://ui-avatars.com/api/?name=${encodeURIComponent(currentUser.displayName||'T')}&background=7c3aed&color=fff`)
        : '';

    const card = document.createElement('div');
    card.className  = 'post-card';
    card.dataset.id = post.id;

    card.innerHTML = `
    <div class="post-header">
        <img src="${avatar}" class="post-avatar" alt="">
        <div class="post-meta">
            <div class="post-author"><a href="profile.html?uid=${post.uid}">${escHtml(post.displayName)}</a></div>
            <div class="post-time">${timeAgo}</div>
        </div>
        <a href="index.html?tag=${post.tag}" class="post-tag-badge ${cat.cls}">
            <i class="${cat.icon}"></i> ${cat.label}
        </a>
        ${isOwner ? `<button class="post-delete" title="Delete post"><i class="fas fa-trash"></i></button>` : ''}
    </div>
    ${post.body ? `<div class="post-body">${escHtml(post.body)}</div>` : ''}
    ${post.videoURL ? buildVideoEmbed(post.videoURL) : ''}
    <div class="post-footer">
        <button class="post-action like-btn ${liked ? 'liked' : ''}">
            <i class="${liked ? 'fas' : 'far'} fa-heart"></i>
            <span class="like-count">${post.likeCount || 0}</span>
        </button>
        <button class="post-action comment-toggle">
            <i class="far fa-comment"></i>
            <span class="comment-count">${post.commentCount || 0}</span>
        </button>
    </div>
    <div class="post-comments" id="comments_${post.id}">
        <div class="comment-list" id="clist_${post.id}"></div>
        <form class="comment-form">
            <img src="${myAvatar}" class="post-avatar" alt="" style="width:30px;height:30px">
            <input type="text" placeholder="Write a comment..." class="comment-input" required>
            <button type="submit" class="btn-comment"><i class="fas fa-paper-plane"></i></button>
        </form>
    </div>`;

    // Rating bar (inserted first — image goes before it)
    const userRating = (post.ratings && currentUser) ? (post.ratings[currentUser.uid] || 0) : 0;
    const ratingAvg  = post.ratingAvg   || 0;
    const ratingCnt  = post.ratingCount || 0;
    const ratingBar  = document.createElement('div');
    ratingBar.className = 'post-rating-bar';
    const starsWrap = document.createElement('div');
    starsWrap.className = 'rating-stars';
    for (let i = 1; i <= 10; i++) {
        const s = document.createElement('button');
        s.className = 'rating-star' + (i <= userRating ? ' my-rated' : '');
        s.textContent = '★';
        s.dataset.val = i;
        starsWrap.appendChild(s);
    }
    const scoreEl = document.createElement('span');
    scoreEl.className = 'rating-score';
    scoreEl.textContent = ratingCnt > 0
        ? `${ratingAvg.toFixed(1)}/10 · ${ratingCnt} rating${ratingCnt !== 1 ? 's' : ''}`
        : 'Rate this';
    ratingBar.appendChild(starsWrap);
    ratingBar.appendChild(scoreEl);
    card.querySelector('.post-footer').before(ratingBar);

    // Image inserted before ratingBar → correct order: body → image → rating → footer
    if (post.imageURL) {
        const img = document.createElement('img');
        img.className = 'post-image';
        img.alt = '';
        img.src = post.imageURL;
        ratingBar.before(img);
    }

    // Star hover + click
    starsWrap.addEventListener('mouseover', e => {
        const s = e.target.closest('.rating-star'); if (!s) return;
        const v = +s.dataset.val;
        starsWrap.querySelectorAll('.rating-star').forEach((el, i) => el.classList.toggle('hovered', i < v));
    });
    starsWrap.addEventListener('mouseleave', () => {
        starsWrap.querySelectorAll('.rating-star').forEach(el => el.classList.remove('hovered'));
    });
    starsWrap.addEventListener('click', async e => {
        const s = e.target.closest('.rating-star'); if (!s || !currentUser) return;
        const v = +s.dataset.val;
        starsWrap.querySelectorAll('.rating-star').forEach((el, i) => {
            el.classList.toggle('my-rated', i < v);
            el.classList.remove('hovered');
        });
        const result = await ratePost(post.id, currentUser, v);
        if (result) scoreEl.textContent = `${result.avg.toFixed(1)}/10 · ${result.count} rating${result.count !== 1 ? 's' : ''}`;
    });

    card.querySelector('.like-btn')?.addEventListener('click', () => toggleLike(post.id, currentUser));

    card.querySelector('.comment-toggle')?.addEventListener('click', () => {
        const box  = card.querySelector(`#comments_${post.id}`);
        const open = box.classList.toggle('open');
        if (open) loadComments(post.id, card.querySelector(`#clist_${post.id}`));
    });

    card.querySelector('.comment-form')?.addEventListener('submit', async e => {
        e.preventDefault();
        const input = e.target.querySelector('.comment-input');
        const text  = input.value.trim();
        if (!text || !currentUser) return;
        input.value = '';
        await postComment(post.id, currentUser, text);
        loadComments(post.id, card.querySelector(`#clist_${post.id}`));
    });

    card.querySelector('.post-delete')?.addEventListener('click', async () => {
        if (!confirm('Delete this post?')) return;
        await remove(dbRef(database, `posts/${post.id}`));
    });

    return card;
}

// ── Like / unlike ─────────────────────────────────────────────
async function toggleLike(postId, user) {
    if (!user) return;
    const likeRef = dbRef(database, `posts/${postId}/likes/${user.uid}`);
    const snap    = await get(likeRef);
    if (snap.exists()) {
        await remove(likeRef);
    } else {
        await set(likeRef, true);
    }
    const likesSnap = await get(dbRef(database, `posts/${postId}/likes`));
    const count     = likesSnap.exists() ? Object.keys(likesSnap.val()).length : 0;
    await set(dbRef(database, `posts/${postId}/likeCount`), count);
}

// ── Comments ──────────────────────────────────────────────────
async function postComment(postId, user, text) {
    await push(dbRef(database, `posts/${postId}/comments`), {
        uid:         user.uid,
        displayName: user.displayName || 'Anonymous',
        photoURL:    user.photoURL    || '',
        body:        text,
        createdAt:   Date.now()
    });
    const countRef  = dbRef(database, `posts/${postId}/commentCount`);
    const countSnap = await get(countRef);
    await set(countRef, (countSnap.val() || 0) + 1);
}

async function loadComments(postId, container) {
    const snap = await get(dbRef(database, `posts/${postId}/comments`));
    container.innerHTML = '';
    if (!snap.exists()) return;
    const comments = [];
    snap.forEach(child => comments.push(child.val()));
    comments.sort((a, b) => a.createdAt - b.createdAt);
    comments.forEach(c => {
        const av  = c.photoURL || `https://ui-avatars.com/api/?name=${encodeURIComponent(c.displayName)}&background=7c3aed&color=fff`;
        const div = document.createElement('div');
        div.className = 'comment-item';
        div.innerHTML = `
        <img src="${av}" class="post-avatar" alt="" style="width:28px;height:28px">
        <div class="comment-bubble">
            <div class="comment-header">
                <span class="comment-author">${escHtml(c.displayName)}</span>
                <span class="comment-time">${formatTime(c.createdAt || Date.now())}</span>
            </div>
            <span class="comment-text">${escHtml(c.body)}</span>
        </div>`;
        container.appendChild(div);
    });
}

// ── Trending ──────────────────────────────────────────────────
export async function loadTrending(container) {
    const snap   = await get(dbRef(database, 'posts'));
    const counts = {};
    if (snap.exists()) {
        snap.forEach(child => {
            const tag = child.val().tag;
            if (tag) counts[tag] = (counts[tag] || 0) + 1;
        });
    }
    const tags   = Object.keys(CATEGORIES).filter(k => k !== 'all');
    const sorted = tags.filter(t => counts[t] > 0).sort((a, b) => counts[b] - counts[a]).slice(0, 8);
    container.innerHTML = '';
    sorted.forEach(tag => {
        const cat = CATEGORIES[tag];
        const n   = counts[tag];
        container.innerHTML += `
        <a href="index.html?tag=${tag}" class="trending-tag">
            <div class="tt-icon ${cat.cls}"><i class="${cat.icon}" style="color:#fff;font-size:.8rem"></i></div>
            <div class="tt-info">
                <div class="tt-name">${cat.label}</div>
                <div class="tt-count">${n} post${n !== 1 ? 's' : ''}</div>
            </div>
        </a>`;
    });
    if (!sorted.length) container.innerHTML = '<p style="color:var(--muted);font-size:.8rem;padding:8px 0">No posts yet.</p>';
}

// ── Image compress → base64 (fallback when Storage unavailable) ──
async function compressImage(file, maxW = 800, quality = 0.72) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        const url = URL.createObjectURL(file);
        img.onload = () => {
            try {
                const scale  = Math.min(1, maxW / img.width);
                const canvas = document.createElement('canvas');
                canvas.width  = Math.round(img.width  * scale);
                canvas.height = Math.round(img.height * scale);
                canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height);
                URL.revokeObjectURL(url);
                resolve(canvas.toDataURL('image/jpeg', quality));
            } catch (e) { reject(e); }
        };
        img.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Image load failed')); };
        img.src = url;
    });
}

// ── Rate a post ───────────────────────────────────────────────
async function ratePost(postId, user, value) {
    if (!user) return null;
    await set(dbRef(database, `posts/${postId}/ratings/${user.uid}`), value);
    const snap = await get(dbRef(database, `posts/${postId}/ratings`));
    if (!snap.exists()) return null;
    const vals  = Object.values(snap.val());
    const count = vals.length;
    const avg   = Math.round((vals.reduce((a, b) => a + b, 0) / count) * 10) / 10;
    await set(dbRef(database, `posts/${postId}/ratingAvg`),   avg);
    await set(dbRef(database, `posts/${postId}/ratingCount`), count);
    return { avg, count };
}

// ── Featured: top-rated per category ─────────────────────────
export async function loadFeatured(container, activeTag) {
    const snap = await get(dbRef(database, 'posts'));
    if (!snap.exists()) { container.innerHTML = '<p style="color:var(--muted);font-size:.8rem;padding:8px 0">No rated posts yet.</p>'; return; }
    const posts = [];
    snap.forEach(child => {
        const v = child.val();
        if ((v.ratingCount || 0) >= 1) {
            if (!activeTag || activeTag === 'all' || v.tag === activeTag) {
                posts.push({ id: child.key, ...v });
            }
        }
    });
    posts.sort((a, b) => (b.ratingAvg || 0) - (a.ratingAvg || 0));
    const top = posts.slice(0, 5);
    container.innerHTML = '';
    if (!top.length) {
        container.innerHTML = '<p style="color:var(--muted);font-size:.8rem;padding:8px 0">Rate some posts to see featured!</p>';
        return;
    }
    top.forEach(p => {
        const cat  = CATEGORIES[p.tag] || CATEGORIES.other;
        const body = (p.body || '').slice(0, 45) + ((p.body || '').length > 45 ? '…' : '');
        const item = document.createElement('a');
        item.href      = `index.html?tag=${p.tag}`;
        item.className = 'featured-item';
        item.innerHTML = `
        <div class="fi-score">${(p.ratingAvg||0).toFixed(1)}<span>/10</span></div>
        <div class="fi-info">
            <div class="fi-author">${escHtml(p.displayName)}</div>
            ${body ? `<div class="fi-body">${escHtml(body)}</div>` : ''}
            <div class="fi-tag ${cat.cls}"><i class="${cat.icon}"></i> ${cat.label}</div>
        </div>`;
        container.appendChild(item);
    });
}

// ── Video embed ───────────────────────────────────────────────
function buildVideoEmbed(url) {
    if (!url) return '';
    // YouTube: watch?v=, youtu.be/, shorts/
    const yt = url.match(/(?:youtube\.com\/(?:watch\?v=|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/);
    if (yt) return `<div class="post-video"><iframe src="https://www.youtube.com/embed/${yt[1]}" allowfullscreen loading="lazy"></iframe></div>`;
    // Vimeo
    const vi = url.match(/vimeo\.com\/(\d+)/);
    if (vi) return `<div class="post-video"><iframe src="https://player.vimeo.com/video/${vi[1]}" allowfullscreen loading="lazy"></iframe></div>`;
    // TikTok
    const tt = url.match(/tiktok\.com\/@[^/]+\/video\/(\d+)/);
    if (tt) return `<div class="post-video-link"><a href="${url}" target="_blank" rel="noopener"><i class="fab fa-tiktok"></i> Watch on TikTok</a></div>`;
    // Generic fallback
    return `<div class="post-video-link"><a href="${url}" target="_blank" rel="noopener"><i class="fas fa-play-circle"></i> Watch Video</a></div>`;
}

// ── Helpers ───────────────────────────────────────────────────
function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function formatTime(ts) {
    const s = Math.floor((Date.now() - ts) / 1000);
    if (s < 60)       return 'Just now';
    if (s < 3600)     return Math.floor(s / 60)       + ' min ago';
    if (s < 86400)    return Math.floor(s / 3600)     + 'h ago';
    if (s < 2592000)  return Math.floor(s / 86400)    + 'd ago';
    if (s < 31536000) return Math.floor(s / 2592000)  + 'mo ago';
    return                    Math.floor(s / 31536000) + 'y ago';
}
