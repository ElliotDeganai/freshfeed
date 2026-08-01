<template>
    <footer class="site-footer">
        <a href="https://ed-factory.com/" target="_blank" rel="noopener noreferrer" class="ed-credit">
            Made by
            <img src="/images/ed-logo.png" alt="ED" class="ed-credit-logo" />
            Web Factory
        </a>
        <div class="footer-divider"></div>
        <p class="footer-copyright">{{ $page.props.site.name }} — {{ new Date().getFullYear() }}</p>
    </footer>
</template>

<style scoped>
.site-footer { text-align: center; padding: 24px 16px; }
.footer-copyright { margin: 0; font-size: 12px; color: #8FA098; }
.ed-credit {
    display: inline-flex; align-items: center; gap: 6px; color: #8FA098; text-decoration: none;
    font-size: 12px; transition: color .15s;
}
.ed-credit:hover { color: #4B5A54; }
.ed-credit-logo { height: 20px; width: 20px; object-fit: contain; }
.footer-divider { width: 96px; height: 1px; background: #EEEFEC; margin: 12px auto; }
</style>
