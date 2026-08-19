const scanFeedbackDelay = 10000;

export default (initialUrl = '') => ({
    loading: false,
    url: initialUrl,
    pasteError: '',
    scanError: '',
    scanMode: false,
    scanStarting: false,
    scanHandled: false,
    scanSession: 0,
    scanFeedbackTimer: null,
    scanner: null,

    async pasteUrl() {
        this.pasteError = '';

        if (!window.isSecureContext) {
            this.pasteError = 'A colagem automática exige HTTPS. Abra a aplicação pelo endereço seguro ou cole a URL manualmente.';
            return;
        }

        if (!navigator.clipboard?.readText) {
            this.pasteError = 'Seu navegador não permite ler a área de transferência automaticamente.';
            return;
        }

        try {
            this.url = (await navigator.clipboard.readText()).trim();
        } catch (error) {
            this.pasteError = error?.name === 'NotAllowedError'
                ? 'O navegador bloqueou o acesso à área de transferência. Permita o acesso para este site ou cole a URL manualmente.'
                : 'Não foi possível acessar a área de transferência. Cole a URL manualmente.';
        }
    },

    async startScanner() {
        if (this.scanStarting || this.scanMode) {
            return;
        }

        this.scanError = '';

        if (!window.isSecureContext) {
            this.scanError = 'A câmera exige uma conexão HTTPS. Abra a aplicação pelo endereço seguro e tente novamente.';
            return;
        }

        if (!navigator.mediaDevices?.getUserMedia) {
            this.scanError = 'A câmera não está disponível neste dispositivo ou navegador. Informe a URL manualmente.';
            return;
        }

        const session = ++this.scanSession;

        this.scanMode = true;
        this.scanStarting = true;
        this.scanHandled = false;

        await this.$nextTick();

        try {
            const { Html5Qrcode, Html5QrcodeSupportedFormats } = await import('html5-qrcode');

            if (!this.isCurrentScan(session)) {
                return;
            }

            const scanner = new Html5Qrcode('nfce-qr-reader', {
                formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE],
                verbose: false,
            });

            this.scanner = scanner;

            await scanner.start(
                { facingMode: 'environment' },
                {
                    fps: 10,
                    qrbox: (width, height) => {
                        const size = Math.floor(Math.min(width, height) * 0.7);

                        return { width: size, height: size };
                    },
                },
                (decodedText) => this.handleScanSuccess(decodedText, session),
                () => {},
            );

            if (!this.isCurrentScan(session)) {
                await this.releaseScanner(scanner);
                return;
            }

            this.scanStarting = false;
            this.scheduleScanFeedback(session);
        } catch (error) {
            if (this.isCurrentScan(session)) {
                this.scanError = this.cameraErrorMessage(error);
                await this.stopScanner();
            }
        } finally {
            if (this.scanSession === session) {
                this.scanStarting = false;
            }
        }
    },

    async handleScanSuccess(decodedText, session) {
        if (!this.isCurrentScan(session) || this.scanHandled) {
            return;
        }

        this.clearScanFeedback();

        const scannedUrl = this.validNfceUrl(decodedText);

        if (!scannedUrl) {
            this.scanError = 'O QR Code foi reconhecido, mas não contém uma URL válida de NFC-e. Tente outro código.';
            return;
        }

        this.scanHandled = true;
        this.scanError = '';
        this.url = scannedUrl;

        await this.stopScanner();
        await this.$nextTick();

        document.getElementById('url')?.focus();
    },

    validNfceUrl(decodedText) {
        const value = String(decodedText ?? '').trim();

        try {
            const parsedUrl = new URL(value);
            const accessKey = parsedUrl.searchParams.get('p')?.split('|')[0]?.trim();
            const hasAllowedPort = parsedUrl.port === '' || parsedUrl.port === '443';

            if (
                parsedUrl.protocol !== 'https:'
                || parsedUrl.username !== ''
                || parsedUrl.password !== ''
                || !hasAllowedPort
                || !/^\d{44}$/.test(accessKey ?? '')
            ) {
                return null;
            }

            return value;
        } catch {
            return null;
        }
    },

    scheduleScanFeedback(session) {
        this.clearScanFeedback();
        this.scanFeedbackTimer = window.setTimeout(() => {
            if (this.isCurrentScan(session) && !this.scanHandled) {
                this.scanError = 'Nenhum QR Code foi reconhecido. Centralize o código, evite reflexos e tente novamente.';
            }
        }, scanFeedbackDelay);
    },

    clearScanFeedback() {
        if (this.scanFeedbackTimer !== null) {
            window.clearTimeout(this.scanFeedbackTimer);
            this.scanFeedbackTimer = null;
        }
    },

    isCurrentScan(session) {
        return this.scanMode && this.scanSession === session;
    },

    async stopScanner() {
        this.scanMode = false;
        this.scanStarting = false;
        this.scanSession++;
        this.clearScanFeedback();

        const scanner = this.scanner;
        this.scanner = null;

        await this.releaseScanner(scanner);
    },

    async releaseScanner(scanner) {
        if (!scanner) {
            return;
        }

        try {
            await scanner.stop();
        } catch {}

        try {
            scanner.clear();
        } catch {}
    },

    cameraErrorMessage(error) {
        const name = String(error?.name ?? '');
        const message = String(error?.message ?? error ?? '').toLowerCase();

        if (name === 'NotAllowedError' || name === 'PermissionDeniedError' || message.includes('permission')) {
            return 'O acesso à câmera foi negado. Permita o acesso nas configurações do navegador e tente novamente.';
        }

        if (name === 'NotFoundError' || name === 'DevicesNotFoundError' || message.includes('not found')) {
            return 'Nenhuma câmera foi encontrada neste dispositivo. Informe a URL manualmente.';
        }

        if (
            name === 'NotReadableError'
            || name === 'TrackStartError'
            || name === 'AbortError'
            || message.includes('could not start video source')
        ) {
            return 'Não foi possível usar a câmera. Verifique se ela já está aberta em outro aplicativo e tente novamente.';
        }

        if (name === 'OverconstrainedError' || name === 'ConstraintNotSatisfiedError') {
            return 'A câmera disponível não é compatível com a leitura. Informe a URL manualmente.';
        }

        return 'Não foi possível iniciar a câmera. Verifique as permissões do navegador ou informe a URL manualmente.';
    },

    destroy() {
        this.stopScanner();
    },
});
