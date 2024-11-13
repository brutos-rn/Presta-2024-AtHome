export function getTokenFromUrl() {
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('_token');
}
