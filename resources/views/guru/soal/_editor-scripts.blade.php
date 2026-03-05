{{-- CKEditor 5 Super Build CDN (includes all open-source plugins) --}}
<script src="https://cdn.ckeditor.com/ckeditor5/41.4.2/super-build/ckeditor.js"></script>

<script>
// ========================================
//  CKEditor 5 Configuration & Initialization
// ========================================
const CK_CONFIG = {
    toolbar: {
        items: [
            'heading', '|',
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'fontSize', 'fontColor', 'fontBackgroundColor', '|',
            'alignment', '|',
            'bulletedList', 'numberedList', 'outdent', 'indent', '|',
            'insertTable', 'imageUpload', 'link', 'blockQuote', '|',
            'subscript', 'superscript', '|',
            'undo', 'redo', '|',
            'sourceEditing'
        ],
        shouldNotGroupWhenFull: true
    },
    image: {
        toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block', 'imageStyle:side'],
        upload: { types: ['jpeg', 'png', 'gif', 'bmp', 'webp', 'svg'] }
    },
    table: {
        contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells', 'tableProperties', 'tableCellProperties']
    },
    simpleUpload: {
        uploadUrl: '{{ route("guru.soal.uploadGambar") }}',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    },
    heading: {
        options: [
            { model: 'paragraph', title: 'Paragraph', class: 'ck-heading_paragraph' },
            { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading_heading2' },
            { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading_heading3' },
            { model: 'heading4', view: 'h4', title: 'Heading 4', class: 'ck-heading_heading4' }
        ]
    },
    fontSize: { options: [10, 12, 14, 'default', 18, 20, 24, 30] },
    alignment: { options: ['left', 'center', 'right', 'justify'] },
    removePlugins: [
        'ExportPdf', 'ExportWord', 'ImportWord', 'AIAssistant',
        'CKBox', 'CKFinder', 'EasyImage', 'MultiLevelList',
        'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges',
        'RealTimeCollaborativeRevisionHistory', 'PresenceList',
        'Comments', 'TrackChanges', 'TrackChangesData', 'RevisionHistory',
        'Pagination', 'WProofreader', 'MathType', 'SlashCommand',
        'Template', 'DocumentOutline', 'FormatPainter', 'TableOfContents',
        'PasteFromOfficeEnhanced', 'CaseChange'
    ],
    language: 'id',
    placeholder: 'Ketik di sini...'
};

const CK_MINI_CONFIG = {
    toolbar: ['bold', 'italic', 'underline', '|', 'subscript', 'superscript', '|', 'imageUpload', 'link', '|', 'undo', 'redo'],
    simpleUpload: {
        uploadUrl: '{{ route("guru.soal.uploadGambar") }}',
        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
    },
    removePlugins: [
        'ExportPdf', 'ExportWord', 'ImportWord', 'AIAssistant',
        'CKBox', 'CKFinder', 'EasyImage', 'MultiLevelList',
        'RealTimeCollaborativeComments', 'RealTimeCollaborativeTrackChanges',
        'RealTimeCollaborativeRevisionHistory', 'PresenceList',
        'Comments', 'TrackChanges', 'TrackChangesData', 'RevisionHistory',
        'Pagination', 'WProofreader', 'MathType', 'SlashCommand',
        'Template', 'DocumentOutline', 'FormatPainter', 'TableOfContents',
        'PasteFromOfficeEnhanced', 'CaseChange'
    ],
    placeholder: 'Ketik opsi jawaban...'
};

// Store editor instances
const editorInstances = {};

// ========================================
//  Initialize CKEditor for a textarea element
// ========================================
function initCKEditor(element, config, key) {
    if (editorInstances[key]) {
        editorInstances[key].destroy().catch(() => {});
        delete editorInstances[key];
    }
    return CKEDITOR.ClassicEditor.create(element, config)
        .then(editor => {
            editorInstances[key] = editor;
            // Sync editor content to original textarea/input on change
            editor.model.document.on('change:data', () => {
                const hidden = document.getElementById(key + '_hidden');
                if (hidden) hidden.value = editor.getData();
            });
            return editor;
        })
        .catch(error => {
            console.error('CKEditor init error for ' + key + ':', error);
        });
}

// ========================================
//  Initialize all editors on page
// ========================================
function initAllEditors() {
    // Main soal editor
    const soalEl = document.querySelector('#soalEditor');
    if (soalEl) {
        initCKEditor(soalEl, { ...CK_CONFIG, placeholder: 'Tulis soal di sini...' }, 'soal');
    }

    // Pembahasan editor
    const pembahasanEl = document.querySelector('#pembahasanEditor');
    if (pembahasanEl) {
        initCKEditor(pembahasanEl, {
            ...CK_CONFIG,
            toolbar: ['bold', 'italic', 'underline', '|', 'bulletedList', 'numberedList', '|',
                       'insertTable', 'imageUpload', 'link', '|', 'subscript', 'superscript', '|', 'undo', 'redo'],
            placeholder: 'Tulis pembahasan (opsional)...'
        }, 'pembahasan');
    }

    // Opsi editors
    document.querySelectorAll('.opsi-editor').forEach((el) => {
        const idx = el.id ? el.id.replace('opsiEditor_', '') : null;
        if (idx !== null) {
            initCKEditor(el, CK_MINI_CONFIG, 'opsi_' + idx);
        }
    });
}

// ========================================
//  Equation Dialog Logic
// ========================================
let activeEditorKey = 'soal';

function openEquationModal(editorKey) {
    activeEditorKey = editorKey || 'soal';
    const modal = new bootstrap.Modal(document.getElementById('equationModal'));
    document.getElementById('latexInput').value = '';
    document.getElementById('equationPreview').innerHTML = '<span class="text-muted">Ketik LaTeX di atas...</span>';
    modal.show();
    setTimeout(() => document.getElementById('latexInput').focus(), 300);
}

(function() {
    const latexInput = document.getElementById('latexInput');
    const preview = document.getElementById('equationPreview');
    const insertBtn = document.getElementById('insertEquation');
    let previewTimeout;

    if (latexInput) {
        latexInput.addEventListener('input', function() {
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(function() {
                const latex = latexInput.value.trim();
                if (!latex) {
                    preview.innerHTML = '<span class="text-muted">Ketik LaTeX di atas...</span>';
                    return;
                }
                preview.innerHTML = '\\[' + latex + '\\]';
                if (window.MathJax && MathJax.typesetPromise) {
                    MathJax.typesetPromise([preview]).catch(function() {
                        preview.innerHTML = '<span class="text-danger">LaTeX tidak valid</span>';
                    });
                }
            }, 400);
        });
    }

    // Quick-insert symbols
    document.querySelectorAll('.eq-quick').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const latex = this.getAttribute('data-latex');
            latexInput.value += (latexInput.value ? ' ' : '') + latex;
            latexInput.dispatchEvent(new Event('input'));
            latexInput.focus();
        });
    });

    // Insert equation into active CKEditor
    if (insertBtn) {
        insertBtn.addEventListener('click', function() {
            const latex = latexInput.value.trim();
            if (!latex) { alert('Masukkan kode LaTeX terlebih dahulu'); return; }

            const editor = editorInstances[activeEditorKey];
            if (editor) {
                const viewFragment = editor.data.processor.toView('<span class="math-tex">\\(' + latex + '\\)</span>');
                const modelFragment = editor.data.toModel(viewFragment);
                editor.model.insertContent(modelFragment);
            }

            bootstrap.Modal.getInstance(document.getElementById('equationModal')).hide();

            // Re-render MathJax in the page
            setTimeout(function() {
                if (window.MathJax && MathJax.typesetPromise) {
                    MathJax.typesetPromise();
                }
            }, 300);
        });
    }
})();

// ========================================
//  Toggle Opsi visibility
// ========================================
function toggleOpsi() {
    const tipe = document.getElementById('tipeSoal').value;
    const opsiContainer = document.getElementById('opsiContainer');
    const jawabanIsian = document.getElementById('jawabanIsianContainer');

    if (tipe === 'essay') {
        opsiContainer.style.display = 'none';
        jawabanIsian.style.display = 'none';
    } else if (tipe === 'isian') {
        opsiContainer.style.display = 'none';
        jawabanIsian.style.display = 'block';
    } else {
        opsiContainer.style.display = 'block';
        jawabanIsian.style.display = 'none';
    }

    // Disable/enable opsi inputs to prevent submission for non-PG types
    const isPG = (tipe === 'pg' || tipe === 'pg_kompleks');
    const opsiList = document.getElementById('opsiList');
    if (opsiList) {
        opsiList.querySelectorAll('input[type="hidden"]').forEach(input => {
            input.disabled = !isPG;
        });
        opsiList.querySelectorAll('.jawaban-check').forEach(input => {
            input.disabled = !isPG;
        });
    }
}

// ========================================
//  Dynamic Opsi Management
// ========================================
var opsiCounter = 5;

function addOpsi() {
    const opsiList = document.getElementById('opsiList');
    if (!opsiList) return;

    const idx = opsiCounter++;
    const count = opsiList.querySelectorAll('.opsi-card').length;
    const label = String.fromCharCode(65 + count);

    const card = document.createElement('div');
    card.className = 'card opsi-card mb-2';
    card.id = 'opsiCard_' + idx;
    card.innerHTML = `
        <div class="card-body py-2">
            <div class="d-flex align-items-start gap-2">
                <div class="pt-2">
                    <span class="badge bg-secondary fs-6 opsi-label">${label}</span>
                </div>
                <div class="flex-grow-1">
                    <div class="opsi-editor" id="opsiEditor_${idx}"></div>
                    <input type="hidden" name="opsi[${idx}][teks]" id="opsi_${idx}_hidden" value="">
                </div>
                <div class="pt-2 d-flex gap-1 align-items-center">
                    <div class="form-check">
                        <input type="checkbox" name="jawaban_benar_pg[]" value="${idx}" class="form-check-input jawaban-check">
                        <label class="form-check-label text-success fw-semibold">Benar</label>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-opsi" onclick="removeOpsi(${idx})" title="Hapus opsi">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    opsiList.appendChild(card);
    initCKEditor(document.getElementById('opsiEditor_' + idx), CK_MINI_CONFIG, 'opsi_' + idx);
    bindCheckboxBehavior();
    updateOpsiLabels();
    updateRemoveButtons();
}

function removeOpsi(idx) {
    const opsiList = document.getElementById('opsiList');
    if (!opsiList || opsiList.querySelectorAll('.opsi-card').length <= 2) {
        alert('Minimal harus ada 2 opsi jawaban.');
        return;
    }

    const key = 'opsi_' + idx;
    if (editorInstances[key]) {
        editorInstances[key].destroy().catch(() => {});
        delete editorInstances[key];
    }

    const card = document.getElementById('opsiCard_' + idx);
    if (card) card.remove();

    updateOpsiLabels();
    updateRemoveButtons();
}

function updateOpsiLabels() {
    document.querySelectorAll('#opsiList .opsi-card').forEach((card, i) => {
        const label = card.querySelector('.opsi-label');
        if (label) label.textContent = String.fromCharCode(65 + i);
    });
}

function updateRemoveButtons() {
    const cards = document.querySelectorAll('#opsiList .opsi-card');
    document.querySelectorAll('#opsiList .btn-remove-opsi').forEach(btn => {
        btn.style.display = cards.length <= 2 ? 'none' : '';
    });
}

function bindCheckboxBehavior() {
    document.querySelectorAll('.jawaban-check').forEach(cb => {
        cb.onclick = function() {
            const tipe = document.getElementById('tipeSoal').value;
            if (tipe === 'pg' && this.checked) {
                document.querySelectorAll('.jawaban-check').forEach(other => {
                    if (other !== this) other.checked = false;
                });
            }
        };
    });
}

// ========================================
//  Sync all editors before form submit
// ========================================
function syncAllEditors() {
    Object.keys(editorInstances).forEach(key => {
        const hidden = document.getElementById(key + '_hidden');
        if (hidden && editorInstances[key]) {
            hidden.value = editorInstances[key].getData();
        }
    });
    return true;
}

// Initialize all editors when DOM is ready
if (typeof CKEDITOR !== 'undefined' && CKEDITOR.ClassicEditor) {
    initAllEditors();
    console.log('CKEditor 5 initialized successfully');
} else {
    console.error('CKEditor 5 failed to load from CDN');
}
</script>
