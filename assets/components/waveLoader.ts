const loader = document.getElementById('wave-loader');

let isVisible = false;

export function showWaveLoader() {
    if (isVisible) return;
    loader?.classList.remove('opacity-0');
    loader?.classList.add('opacity-100');
    isVisible = true;
}

export function hideWaveLoader() {
    if (!isVisible) return;
    loader?.classList.remove('opacity-100');
    loader?.classList.add('opacity-0');
    isVisible = false;
}
