 // Hàm thêm tag vào textarea
    function addTag(text) {
        const textarea = document.getElementById('commentBox');
        if (textarea.value.includes(text)) return;
        textarea.value += (textarea.value ? ', ' : '') + text;
    }

    // Hiển thị tên file khi chọn
    document.getElementById('mediaInput').addEventListener('change', function() {
        const fileName = this.files[0] ? this.files[0].name : '';
        document.getElementById('fileNameDisplay').textContent = fileName ? 'Đã chọn: ' + fileName : '';
    });