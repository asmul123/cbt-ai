{{-- Equation Editor Modal --}}
<div class="modal fade" id="equationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-calculator"></i> Equation Editor (LaTeX)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold">Kode LaTeX:</label>
                    <textarea id="latexInput" class="form-control font-monospace" rows="3" placeholder="Contoh: \frac{-b \pm \sqrt{b^2-4ac}}{2a}"></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Preview:</label>
                    <div id="equationPreview" class="border rounded p-3 bg-light text-center" style="min-height:60px;font-size:1.3rem;">
                        <span class="text-muted">Ketik LaTeX di atas...</span>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Simbol cepat:</label>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\frac{a}{b}">a/b</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\sqrt{x}">√x</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\sqrt[n]{x}">ⁿ√x</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="x^{2}">x²</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="x^{n}">xⁿ</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="x_{i}">xᵢ</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\sum_{i=1}^{n}">Σ</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\int_{a}^{b}">∫</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\lim_{x \to \infty}">lim</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\infty">∞</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\alpha">&alpha;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\beta">&beta;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\gamma">&gamma;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\theta">&theta;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\pi">&pi;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\Delta">&Delta;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\pm">&pm;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\times">&times;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\div">&div;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\neq">&ne;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\leq">&le;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\geq">&ge;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\rightarrow">&rarr;</button>
                        <button type="button" class="btn btn-outline-secondary btn-sm eq-quick" data-latex="\Rightarrow">&rArr;</button>
                    </div>
                </div>
                <div class="mb-2">
                    <label class="form-label small text-muted">Template rumus:</label>
                    <div class="d-flex flex-wrap gap-1">
                        <button type="button" class="btn btn-outline-info btn-sm eq-quick" data-latex="\frac{-b \pm \sqrt{b^2-4ac}}{2a}">Kuadratik</button>
                        <button type="button" class="btn btn-outline-info btn-sm eq-quick" data-latex="a^2 + b^2 = c^2">Pythagoras</button>
                        <button type="button" class="btn btn-outline-info btn-sm eq-quick" data-latex="\sin^2\theta + \cos^2\theta = 1">Trigonometri</button>
                        <button type="button" class="btn btn-outline-info btn-sm eq-quick" data-latex="\log_a{b} = \frac{\ln{b}}{\ln{a}}">Logaritma</button>
                        <button type="button" class="btn btn-outline-info btn-sm eq-quick" data-latex="\binom{n}{k} = \frac{n!}{k!(n-k)!}">Kombinasi</button>
                        <button type="button" class="btn btn-outline-info btn-sm eq-quick" data-latex="\begin{pmatrix} a & b \\ c & d \end{pmatrix}">Matriks</button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="insertEquation"><i class="bi bi-plus-circle"></i> Sisipkan</button>
            </div>
        </div>
    </div>
</div>
