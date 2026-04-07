/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
    ],
    theme: {
        extend: {
            fontFamily: {
                serif: ['Playfair Display', 'Georgia', 'serif'],
                sans:  ['Inter', 'system-ui', 'sans-serif'],
            },
            colors: {
                'ink':      '#0a0a0a',
                'ink-soft': '#111111',
                'ink-mute': '#1a1a1a',
                'border':   '#2a2a2a',
                'text':     '#f5f5f0',
                'muted':    '#888888',
                'accent':   '#c9a84c',
            },
        },
    },
    plugins: [],
}
