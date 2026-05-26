// ==========================================
// 1. PERSISTENCIA DEL MODO OSCURO (GLOBAL)
// ==========================================
document.addEventListener('DOMContentLoaded', () => {
    const html = document.documentElement;
    const iconSun = document.getElementById('iconSun');
    const iconMoon = document.getElementById('iconMoon');
    
    const savedTheme = localStorage.getItem('theme');
    
    if (savedTheme === 'dark') {
        html.classList.add('dark');
        if(iconSun) iconSun.style.display = 'block';
        if(iconMoon) iconMoon.style.display = 'none';
    } else {
        html.classList.remove('dark');
        if(iconSun) iconSun.style.display = 'none';
        if(iconMoon) iconMoon.style.display = 'block';
    }
});

function toggleTheme() {
    const html = document.documentElement;
    const isDark = html.classList.contains('dark');
    const iconSun = document.getElementById('iconSun');
    const iconMoon = document.getElementById('iconMoon');
    
    if (isDark) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light'); 
        if(iconSun) iconSun.style.display = 'none';
        if(iconMoon) iconMoon.style.display = 'block';
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark'); 
        if(iconSun) iconSun.style.display = 'block';
        if(iconMoon) iconMoon.style.display = 'none';
    }
}