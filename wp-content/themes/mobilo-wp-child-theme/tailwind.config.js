/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        "./views/**/*.php",
    ],
    theme: {
        extend: {
            colors: {
                primary: 'var(--color-primary)',
                secondary: 'var(--color-secondary)',
                tertiary: 'var(--color-tertiary)',
                quaternary: 'var(--color-quaternary)',
                quinary: 'var(--color-quinary)'
            }
        },
    },
    plugins: [],
}
