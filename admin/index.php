<?php
require_once '../config.php';
requireLogin();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen" x-data="qrManager()">
    
    <!-- Header -->
    <header class="bg-white shadow-sm sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center">
                <div class="flex items-center space-x-3">
                    <div class="p-2 bg-indigo-100 rounded-lg">
                        <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">QR Code Manager</h1>
                        <p class="text-sm text-gray-500">Dynamische QR Codes verwalten</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <button @click="showModal = true; resetForm();" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center space-x-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        <span>Neuer QR Code</span>
                    </button>
                    <a href="change-password.php" class="text-gray-600 hover:text-gray-900 transition" title="Passwort ändern">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                    </a>
                    <a href="login.php?logout" class="text-gray-600 hover:text-gray-900 transition" title="Abmelden">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Loading State -->
        <div x-show="loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
            <p class="mt-4 text-gray-600">Lade QR Codes...</p>
        </div>

        <!-- QR Codes Grid -->
        <div x-show="!loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="qr in qrCodes" :key="qr.id">
                <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition overflow-hidden">
                    <!-- QR Code Image -->
                    <div class="bg-gradient-to-br from-indigo-50 to-blue-50 p-6 flex justify-center items-center">
                        <canvas :id="'qr-' + qr.id" class="w-48 h-48"></canvas>
                    </div>
                    
                    <!-- Card Content -->
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900" x-text="qr.titel || 'Ohne Titel'"></h3>
                                <p class="text-sm text-gray-500 font-mono" x-text="'/' + qr.shortcode"></p>
                            </div>
                            <span 
                                class="px-3 py-1 text-xs font-semibold rounded-full"
                                :class="qr.aktiv ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                x-text="qr.aktiv ? 'Aktiv' : 'Inaktiv'"
                            ></span>
                        </div>
                        
                        <p class="text-sm text-gray-600 mb-4 line-clamp-2" x-text="qr.beschreibung || 'Keine Beschreibung'"></p>
                        
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-500 mb-1">Aktuelles Ziel:</p>
                            <a :href="qr.ziel_url" target="_blank" class="text-sm text-indigo-600 hover:text-indigo-800 break-all" x-text="qr.ziel_url"></a>
                        </div>
                        
                        <!-- Stats -->
                        <div class="flex items-center space-x-4 text-sm text-gray-600 mb-4">
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                                <span x-text="qr.scans + ' Scans'"></span>
                            </div>
                            <div class="flex items-center space-x-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span x-text="formatDate(qr.erstellt_am)"></span>
                            </div>
                        </div>
                        
                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button 
                                @click="editQR(qr)"
                                class="flex-1 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium"
                            >
                                Bearbeiten
                            </button>
                            <button 
                                @click="toggleActive(qr.id)"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                                :title="qr.aktiv ? 'Deaktivieren' : 'Aktivieren'"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="qr.aktiv ? 'M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21' : 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'"></path>
                                </svg>
                            </button>
                            <button 
                                @click="downloadQR(qr)"
                                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition"
                                title="QR Code herunterladen"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </button>
                            <button 
                                @click="deleteQR(qr.id)"
                                class="px-4 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 transition"
                                title="Löschen"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="!loading && qrCodes.length === 0" class="text-center py-12">
            <svg class="w-24 h-24 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
            </svg>
            <h3 class="text-xl font-semibold text-gray-700 mb-2">Noch keine QR Codes</h3>
            <p class="text-gray-500 mb-6">Erstelle deinen ersten dynamischen QR Code!</p>
            <button @click="showModal = true; resetForm();" class="bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition">
                Jetzt erstellen
            </button>
        </div>

    </main>

    <!-- Modal für Create/Edit -->
    <div 
        x-show="showModal" 
        x-cloak
        @click.self="showModal = false"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50"
        style="display: none;"
    >
        <div 
            class="bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
            @click.stop
        >
            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-900" x-text="editMode ? 'QR Code bearbeiten' : 'Neuer QR Code'"></h2>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form @submit.prevent="saveQR()" class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titel</label>
                    <input 
                        type="text" 
                        x-model="form.titel"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="z.B. Workshop-Material"
                        required
                    >
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Shortcode (optional)</label>
                    <div class="flex items-center space-x-2">
                        <span class="text-gray-500">/</span>
                        <input 
                            type="text" 
                            x-model="form.shortcode"
                            :disabled="editMode"
                            class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed"
                            placeholder="auto-generiert wenn leer"
                            pattern="[a-zA-Z0-9-_]+"
                        >
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Nur Buchstaben, Zahlen, Bindestriche und Unterstriche</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ziel-URL <span class="text-red-500">*</span></label>
                    <input 
                        type="url" 
                        x-model="form.ziel_url"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="https://example.com"
                        required
                    >
                    <p class="mt-1 text-sm text-gray-500">Wohin soll der QR Code aktuell führen?</p>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Beschreibung (optional)</label>
                    <textarea 
                        x-model="form.beschreibung"
                        rows="3"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        placeholder="Wofür wird dieser QR Code verwendet?"
                    ></textarea>
                </div>
                
                <div class="flex space-x-3 pt-4">
                    <button 
                        type="submit"
                        :disabled="saving"
                        class="flex-1 bg-indigo-600 text-white px-6 py-3 rounded-lg hover:bg-indigo-700 transition font-medium disabled:opacity-50"
                        x-text="saving ? 'Speichere...' : (editMode ? 'Änderungen speichern' : 'QR Code erstellen')"
                    ></button>
                    <button 
                        type="button"
                        @click="showModal = false"
                        class="px-6 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition"
                    >
                        Abbrechen
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function qrManager() {
            return {
                qrCodes: [],
                loading: true,
                showModal: false,
                editMode: false,
                saving: false,
                form: {
                    id: null,
                    titel: '',
                    shortcode: '',
                    ziel_url: '',
                    beschreibung: ''
                },
                
                async init() {
                    await this.loadQRCodes();
                },
                
                async loadQRCodes() {
                    try {
                        this.loading = true;
                        const response = await fetch('api.php?action=list');
                        const result = await response.json();

                        if (result.success) {
                            this.qrCodes = result.data;
                            this.loading = false;
                            await this.$nextTick();
                            this.renderAllQRCodes();
                        }
                    } catch (error) {
                        console.error('Fehler beim Laden:', error);
                        alert('Fehler beim Laden der QR Codes');
                    } finally {
                        this.loading = false;
                    }
                },
                
                renderAllQRCodes() {
                    this.qrCodes.forEach(qr => {
                        const canvas = document.getElementById('qr-' + qr.id);
                        if (canvas) {
                            const url = '<?php echo BASE_URL; ?>/redirect.php?code=' + qr.shortcode;
                            QRCode.toCanvas(canvas, url, {
                                width: 192,
                                margin: 2,
                                color: {
                                    dark: '#4F46E5',
                                    light: '#FFFFFF'
                                }
                            });
                        }
                    });
                },
                
                resetForm() {
                    this.form = {
                        id: null,
                        titel: '',
                        shortcode: '',
                        ziel_url: '',
                        beschreibung: ''
                    };
                    this.editMode = false;
                },
                
                editQR(qr) {
                    this.form = {
                        id: qr.id,
                        titel: qr.titel,
                        shortcode: qr.shortcode,
                        ziel_url: qr.ziel_url,
                        beschreibung: qr.beschreibung
                    };
                    this.editMode = true;
                    this.showModal = true;
                },
                
                async saveQR() {
                    try {
                        this.saving = true;
                        const action = this.editMode ? 'update' : 'create';
                        
                        const response = await fetch('api.php?action=' + action, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(this.form)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            this.showModal = false;
                            await this.loadQRCodes();
                        } else {
                            alert('Fehler: ' + (result.error || 'Unbekannter Fehler'));
                        }
                    } catch (error) {
                        console.error('Fehler beim Speichern:', error);
                        alert('Fehler beim Speichern');
                    } finally {
                        this.saving = false;
                    }
                },
                
                async toggleActive(id) {
                    if (confirm('Status wirklich ändern?')) {
                        try {
                            const response = await fetch('api.php?action=toggle&id=' + id, {
                                method: 'POST'
                            });
                            const result = await response.json();
                            
                            if (result.success) {
                                await this.loadQRCodes();
                            }
                        } catch (error) {
                            console.error('Fehler:', error);
                            alert('Fehler beim Ändern des Status');
                        }
                    }
                },
                
                async deleteQR(id) {
                    if (confirm('QR Code wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden!')) {
                        try {
                            const response = await fetch('api.php?action=delete&id=' + id, {
                                method: 'POST'
                            });
                            const result = await response.json();
                            
                            if (result.success) {
                                await this.loadQRCodes();
                            }
                        } catch (error) {
                            console.error('Fehler:', error);
                            alert('Fehler beim Löschen');
                        }
                    }
                },
                
                downloadQR(qr) {
                    const canvas = document.getElementById('qr-' + qr.id);
                    if (canvas) {
                        const url = canvas.toDataURL('image/png');
                        const link = document.createElement('a');
                        link.download = 'qr-' + qr.shortcode + '.png';
                        link.href = url;
                        link.click();
                    }
                },
                
                formatDate(dateString) {
                    const date = new Date(dateString);
                    return date.toLocaleDateString('de-DE', { 
                        day: '2-digit', 
                        month: '2-digit', 
                        year: 'numeric' 
                    });
                }
            }
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
        .line-clamp-2 {
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
    </style>
</body>
</html>
