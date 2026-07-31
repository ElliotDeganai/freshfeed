/**
 * Palette d'avatars colorés reprise du mockup NutriShare (fond pastel + texte foncé assorti).
 * Utilisée pour les avatars d'utilisateurs et les badges de catégories dans toute la zone admin.
 */
const AVATAR_PALETTE = [
    { bg: '#E1F5EE', text: '#0F6E56' }, // teal
    { bg: '#EEEDFE', text: '#534AB7' }, // purple
    { bg: '#FAEEDA', text: '#854F0B' }, // amber
    { bg: '#FAECE7', text: '#993C1D' }, // coral
    { bg: '#FBEAF0', text: '#993556' }, // pink
    { bg: '#E6F1FB', text: '#0C447C' }, // blue
];

export function avatarColor(seed) {
    const str = String(seed ?? '');
    let hash = 0;
    for (let i = 0; i < str.length; i++) {
        hash = (hash * 31 + str.charCodeAt(i)) >>> 0;
    }
    return AVATAR_PALETTE[hash % AVATAR_PALETTE.length];
}

export function initials(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/);
    return ((parts[0]?.[0] ?? '') + (parts[1]?.[0] ?? '')).toUpperCase();
}
