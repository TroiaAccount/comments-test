<template>
    <form @submit.prevent="addComment" class="mt-4">
        <!-- Your existing form fields -->
        <div class="mb-3">
            <label for="userName" class="form-label">User Name*:</label>
            <input type="text" class="form-control" id="userName" v-model="userName" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email*:</label>
            <input type="email" class="form-control" id="email" v-model="email" required>
        </div>
        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label for="text" class="form-label mb-0">Text*:</label>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary" @click="insertTag('i')">i</button>
                    <button type="button" class="btn btn-primary" @click="insertTag('strong')">strong</button>
                    <button type="button" class="btn btn-primary" @click="insertLink()">a</button>
                </div>
            </div>
            <textarea class="form-control" id="text" v-model="text" required></textarea>
        </div>
        <div class="mb-3">
            <label for="home_page" class="form-label">Home page:</label>
            <textarea class="form-control" id="home_page" v-model="home_page"></textarea>
        </div>
        <div class="mb-3">
            <label for="fileInput" class="form-label">Choose File (JPG, GIF, PNG max 320х240px or TXT max
                100KB):</label>
            <input type="file" id="fileInput" ref="fileInput">
        </div>
        <div class="d-flex justify-content-between">
            <button type="button" @click="is_preview = !is_preview" class="btn btn-secondary">Preview</button>
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
    <div v-show="reply_to" class="mt-3">
        <p>
            Reply to: {{ reply_to }} <a href="#" @click="dropParentId" class="text-danger">Drop</a>
        </p>
    </div>
    <div class="mt-3" v-show="is_preview">
        <h4>Preview:</h4>
        <div v-html="preview"></div>
    </div>
</template>

<script>

export default {
    emits: ['addComment', 'dropParentId'],

    data() {
        return {
            userName: '',
            email: '',
            text: '',
            home_page: '',
            preview: '',
            is_preview: false
        };
    },
    props: {
        reply_to: {
            type: Number
        }
    },
    methods: {
        resizeImage(file, callback) {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = () => {
                const maxWidth = 320;
                const maxHeight = 240;
                let width = img.width;
                let height = img.height;

                if (width > maxWidth || height > maxHeight) {
                    if (width / maxWidth > height / maxHeight) {
                        height *= maxWidth / width;
                        width = maxWidth;
                    } else {
                        width *= maxHeight / height;
                        height = maxHeight;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob((blob) => {
                    const resizedFile = new File([blob], file.name, {type: file.type});
                    callback(resizedFile);
                }, file.type);
            };

            img.src = URL.createObjectURL(file);
        },
        getImageDimensions(file, callback) {
            const img = new Image();
            img.onload = () => {
                callback(img.width, img.height);
            };
            img.src = URL.createObjectURL(file);
        },
        validateText() {
            const unclosedTagRegex = /\[(\w+)(?:\s+[^\]]+)?\](?![\s\S]*\[\/\1\])/g;

            // Проверяем, есть ли открытые теги без закрытия
            const unclosedTags = this.text.match(unclosedTagRegex);

            if (unclosedTags) {
                alert(`Unclosed tags found: ${unclosedTags.join(', ')}`);
                return false; // Если есть незакрытые теги, прекращаем отправку комментария
            }

            return true
        },
        validatePreview() {
            // Регулярное выражение для поиска всех тегов в предпросмотре
            const tagRegex = /<(\w+)(?:\s+[^>]+)?>.*?<\/\1>/g;

            // Проверяем, есть ли какие-то теги, кроме разрешенных
            const invalidTags = [];
            let match;
            while ((match = tagRegex.exec(this.preview)) !== null) {
                const tagName = match[1].toLowerCase();
                if (tagName !== 'strong' && tagName !== 'a' && tagName !== 'i') {
                    invalidTags.push(tagName);
                }
            }

            if (invalidTags.length > 0) {
                alert(`Invalid tags found: ${invalidTags.join(', ')}`);
                return false; // Если есть недопустимые теги, прекращаем отправку комментария
            }
            return true;
        },
        addComment() {
            if (this.validateText() && this.validatePreview() && this.validateInput()) {

                const fileInput = this.$refs.fileInput.files[0];
                const formData = new FormData();
                formData.append('user_name', this.userName);
                formData.append('email', this.email);
                formData.append('text', encodeURIComponent(this.preview));
                formData.append('home_page', this.home_page);
                let success = false

                if (fileInput) {
                    console.log(2);
                    const fileType = fileInput.type;
                    const fileSize = fileInput.size;

                    if ((fileType === "image/jpeg" || fileType === "image/gif" || fileType === "image/png")) {
                        console.log(3);
                        this.getImageDimensions(fileInput, (width, height) => {
                            console.log(4);
                            if (width > 320 || height > 240) {
                                console.log(5);
                                this.resizeImage(fileInput, (resizedFile) => {

                                    formData.append('file', resizedFile);
                                    this.$emit('addComment', formData)
                                    this.clearForm()
                                });
                            } else {
                                console.log(6);
                                formData.append('file', fileInput);
                                this.$emit('addComment', formData)
                                this.clearForm()
                            }
                        });
                    } else if (fileType === "text/plain" && fileSize <= 102400) {
                        formData.append('file', fileInput);
                        this.$emit('addComment', formData)
                        this.clearForm()
                    } else {
                        this.showInvalidFileError();
                    }
                } else {
                    this.$emit('addComment', formData)
                    this.clearForm()
                }

            }
        },


        showInvalidFileError() {
            alert(
                "Invalid file type or size. Please upload an image (JPG, GIF, PNG max 320х240px) or a text file (TXT max 100KB)."
            );
        },
        dropParentId() {
            this.$emit('dropParentId');
        },
        clearForm() {
            this.userName = null;
            this.email = null;
            this.text = null;
            this.home_page = null;
            const fileInput = this.$refs.fileInput;
            if (fileInput) {
                fileInput.value = ''; // Очистить значение поля для файла
            }

        },
        insertTag(tag) {
            const textarea = document.getElementById('text');
            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            this.text = textarea.value.substring(0, startPos) + '[' + tag + ']' + textarea.value.substring(startPos, endPos) + '[/' + tag + ']' + textarea.value.substring(endPos);
        },
        insertLink() {
            const url = prompt('Enter URL:');
            if (!url) {
                return; // Если URL не введён, прекращаем операцию
            }

            // Валидация URL с помощью регулярного выражения
            const urlRegex = /^(ftp|http|https):\/\/[^ "]+$/;
            if (!urlRegex.test(url)) {
                alert("Please enter a valid URL.");
                return;
            }

            const name = prompt('Enter name:');
            if (!name) {
                return; // Если название не введено, прекращаем операцию
            }

            const title = prompt('Enter title (optional):');
            const textarea = document.getElementById('text');
            const startPos = textarea.selectionStart;
            const endPos = textarea.selectionEnd;
            const link = title ? `[a title="${title}" href="${url}"]${name}[/a]` : `[a href="${url}"]${name}[/a]`;
            this.text = textarea.value.substring(0, startPos) + link + textarea.value.substring(endPos);
        },
        previewMessage() {
            if (this.text) {
                // Регулярное выражение для замены тегов в квадратных скобках на их HTML-аналоги
                const tagRegex = /\[(\w+)(?:\s+([^[\]]+))?\]([\s\S]*?)\[\/\1\]/g;
                this.preview = this.text.replace(tagRegex, (match, tag, attr, content) => {
                    switch (tag) {
                        case 'a':
                            return `<a ${attr}>${content}</a>`;
                        case 'i':
                            return `<i>${content}</i>`;
                        case 'strong':
                            return `<strong>${content}</strong>`;
                        default:
                            return match;
                    }
                }).replace(/\n/g, "<br>"); // Замена символов новой строки на теги <br>
            } else {
                this.preview = ''; // Если текст пустой, очистить предварительный просмотр
            }
        },
        validateInput() {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(this.email)) {
                alert("Please enter a valid email address.");
                return false;
            }

            const urlRegex = /^(ftp|http|https):\/\/[^ "]+$/;
            if (this.home_page && !urlRegex.test(this.home_page)) {
                alert("Please enter a valid URL for the home page.");
                return false;
            }

            return true;
        }


    },
    watch: {
        text(newVal) {
            this.previewMessage();
        }
    }
}
</script>
