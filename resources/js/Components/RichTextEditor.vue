<template>
    <div class="rte">
        <div class="rte-toolbar">
            <button type="button" class="rte-btn" @mousedown.prevent="exec('bold')" title="Gras"><i class="ti ti-bold"></i></button>
            <button type="button" class="rte-btn" @mousedown.prevent="exec('italic')" title="Italique"><i class="ti ti-italic"></i></button>
            <button type="button" class="rte-btn" @mousedown.prevent="exec('insertUnorderedList')" title="Liste à puces"><i class="ti ti-list"></i></button>
            <button type="button" class="rte-btn" @mousedown.prevent="exec('insertOrderedList')" title="Liste numérotée"><i class="ti ti-list-numbers"></i></button>
            <button type="button" class="rte-btn" @mousedown.prevent="exec('formatBlock', '<h3>')" title="Titre"><i class="ti ti-heading"></i></button>
            <button type="button" class="rte-btn" @mousedown.prevent="exec('removeFormat')" title="Effacer le style"><i class="ti ti-clear-formatting"></i></button>
        </div>
        <div
            ref="editor"
            class="rte-content"
            contenteditable="true"
            :data-placeholder="placeholder"
            @input="onInput"
        ></div>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: { type: String, default: '' },
        placeholder: { type: String, default: 'Décris ta recette...' },
    },
    emits: ['update:modelValue'],
    mounted() {
        // Initialisation unique — on ne resynchronise plus jamais innerHTML depuis
        // modelValue après coup, pour ne jamais risquer de couper la frappe en cours
        // (écrire dans innerHTML replace tout le contenu et fait perdre le curseur).
        if (this.modelValue) {
            this.$refs.editor.innerHTML = this.modelValue;
        }
    },
    methods: {
        exec(command, value = null) {
            this.$refs.editor.focus();
            document.execCommand(command, false, value);
            this.onInput();
        },
        onInput() {
            this.$emit('update:modelValue', this.$refs.editor.innerHTML);
        },
    },
};
</script>

<style scoped>
.rte { border: 0.5px solid #D9DDD9; border-radius: 10px; overflow: hidden; background: #fff; }
.rte-toolbar { display: flex; gap: 2px; padding: 6px; border-bottom: 0.5px solid #E7E9E7; background: #FAFBFA; }
.rte-btn {
    width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;
    border-radius: 6px; border: none; background: transparent; color: #6B7B74; cursor: pointer; font-size: 14px;
}
.rte-btn:hover { background: #F0F1F0; color: #10241D; }
.rte-content {
    min-height: 160px; padding: 12px 14px; font-size: 13.5px; line-height: 1.6; color: #10241D; outline: none;
}
.rte-content:empty::before {
    content: attr(data-placeholder); color: #ADB6AF;
}
.rte-content :deep(h3) { font-size: 15px; font-weight: 500; margin: 10px 0 6px; }
.rte-content :deep(ul), .rte-content :deep(ol) { padding-left: 20px; margin: 6px 0; }
</style>
