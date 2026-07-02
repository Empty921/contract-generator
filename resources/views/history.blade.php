@extends('dashboard')

@section('title', 'История')

@section('sidebar')
    @parent
@endsection

@section('content')
<div class="container">
    <div class="header">
        <h1>История</h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($documents->isEmpty())
        <div class="no-documents">
            <p>Нет сгенерированных документов.</p>
        </div>
    @else
        <table class="documents-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Шаблон</th>
                    <th>Формат</th>
                    <th>Дата</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $document)
                    <tr>
                        <td>{{ $document->id }}</td>
                        <td>{{ $document->template->name }}</td>
                        <td>{{ strtoupper($document->output_format) }}</td>
                        <td>{{ $document->created_at->format('d.m.Y H:i') }}</td>
                        <td class="actions">
                            <button class="btn btn-preview" onclick="showPreview({{ $document->id }})">Предпросмотр</button>
                            <a href="{{ route('generated-document.show', $document->id) }}" class="btn btn-download">Скачать</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

<!-- Modal for preview -->
<div id="previewModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closePreview()">&times;</span>
        <div id="previewContent"></div>
    </div>
</div>

<script>
function showPreview(documentId) {
    const modal = document.getElementById('previewModal');
    const previewContent = document.getElementById('previewContent');
    
    previewContent.innerHTML = '<p>Загрузка...</p>';
    modal.style.display = 'block';
    
    fetch('/generated-document/' + documentId + '/preview')
        .then(response => response.text())
        .then(html => {
            previewContent.innerHTML = html;
        })
        .catch(error => {
            previewContent.innerHTML = '<p class="error">Ошибка при загрузке предпросмотра.</p>';
            console.error('Preview error:', error);
        });
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
}

// Close modal on click outside
window.onclick = function(event) {
    const modal = document.getElementById('previewModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<style>
.container {
    max-width: 1000px;
    margin: 0 auto;
}

.header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 30px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.header h1 {
    color: white;
    margin: 0;
    font-size: 2.5em;
}

.alert {
    padding: 15px 20px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 1.1em;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.no-documents {
    text-align: center;
    padding: 60px 20px;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 10px;
    margin: 20px 0;
}

.no-documents p {
    font-size: 1.3em;
    color: #333;
}

.documents-table {
    width: 100%;
    background: white;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin: 20px 0;
}

.documents-table thead {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.documents-table th {
    padding: 15px 20px;
    text-align: left;
    font-weight: 600;
}

.documents-table td {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
}

.documents-table tr:last-child td {
    border-bottom: none;
}

.documents-table tr:hover {
    background-color: #f8f9fa;
}

.actions {
    display: flex;
    gap: 10px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.95em;
    transition: all 0.3s ease;
    font-weight: 500;
}

.btn:active {
    transform: scale(0.95);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.btn-preview {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.btn-preview:hover {
    box-shadow: 0 4px 8px rgba(240, 147, 251, 0.4);
}

.btn-download {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.btn-download:hover {
    box-shadow: 0 4px 8px rgba(79, 172, 254, 0.4);
}

.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.7);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 30px;
    border-radius: 10px;
    width: 80%;
    max-width: 900px;
    max-height: 90vh;
    overflow: auto;
    position: relative;
}

.close {
    position: absolute;
    top: 15px;
    right: 20px;
    font-size: 28px;
    font-weight: bold;
    color: #aaa;
    cursor: pointer;
}

.close:hover {
    color: #333;
}

.error {
    color: #dc3545;
    text-align: center;
    padding: 20px;
}
</style>
@endsection
