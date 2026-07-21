<script>
    (function () {
        var isDark = localStorage.getItem('theme') === 'dark';
        document.documentElement.classList.toggle('dark', isDark);
    })();
</script>
