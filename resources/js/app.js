import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * メンバー一覧の表示形式(表形式/カード形式)。切り替えボタンはタイトル
 * バー領域、表示自体は本文領域と、レイアウト上は別ツリーになるため
 * Alpineのstoreで状態を共有する。
 */
Alpine.store('membersView', {
    mode: localStorage.getItem('membersViewMode') || 'table',

    set(mode) {
        this.mode = mode;
        localStorage.setItem('membersViewMode', mode);
    },
});

/**
 * Submits a multipart form via XHR so the upload progress can be tracked,
 * then swaps in the server's response as-is (redirect target on success,
 * or the same form re-rendered with validation errors) — mirrors a normal
 * form submission, just with a progress bar during the upload itself.
 */
Alpine.data('uploadProgress', () => ({
    uploading: false,
    percent: 0,

    submitViaXhr(event) {
        const form = event.target;
        const xhr = new XMLHttpRequest();

        this.uploading = true;
        this.percent = 0;

        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                this.percent = Math.round((e.loaded / e.total) * 100);
            }
        });

        xhr.addEventListener('load', () => {
            document.open();
            document.write(xhr.responseText);
            document.close();
            history.replaceState(null, '', xhr.responseURL || form.action);
        });

        xhr.addEventListener('error', () => {
            this.uploading = false;
            alert('アップロードに失敗しました。通信環境をご確認のうえ、もう一度お試しください。');
        });

        xhr.open(form.method || 'POST', form.action, true);
        xhr.send(new FormData(form));
    },
}));

Alpine.start();
