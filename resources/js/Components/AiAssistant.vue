<template>
    <div class="ai-assistant border border-atechBlue-dark max-w-[98%] rounded-lg p-4 bg-black/25 relative z-2 mx-[1%] text-lg">
        <div ref="chatHistory" class="p-2 bg-atechBlue chat-history max-h-[69vh] rounded-lg overflow-y-auto mb-4 scrollbar-thin scrollbar-thumb-[#2c3e50] scrollbar-track-black">
            <div class="assistant-message text-black bg-gradient-to-r from-atechBlue-light/85 to-[#dddddd] p-4 mr-1 rounded-lg inline-block max-w-[95%] float-left mb-2 text-left">
                <p>Hi! I'm the Grants AI Assistant, based on OpenAI's GPT-4o-mini model. I can help you with the grants in your search results. Click "Add to AI Chatbot" next to a grant to add it to our conversation.</p>
            </div>
            <div v-for="(message, index) in messages" :key="index" class="message-item mb-2 clear-both">
                <p v-if="message.role === 'user'" class="user-message tex-black bg-gradient-to-r from-atechBlue/75 to-atechBlue-dark/50 p-4 ml-2 rounded-lg inline-block max-w-[95%] float-right mb-2 text-right">
                    {{ message.content }}
                </p>
                <div v-if="message.role === 'assistant'" class="assistant-message text-black bg-gradient-to-r from-atechBlue-light/85 to-[#dddddd] p-4 mr-1 rounded-lg inline-block max-w-[95%] float-left mb-2 text-left">
                    <div v-html="renderMarkdown(message.content)"></div>
                </div>
            </div>
            <div v-if="loading" class="loading-indicator text-black mt-4 italic">
                <p>...</p>
            </div>
        </div>

        <!-- Display grants if they are not empty -->
        <div class="grants-section mb-4">
            <div v-if="grants != null && grants.length > 0" class="grants-list">
                <h4 class="text-black font-bold">Selected Grants:</h4>
                <ul>
                    <li v-for="(grant, index) in grants" :key="'grant-' + index" class="text-black flex items-center bg-gradient-to-r from-atechBlue-light/85 to-[#dddddd] p-2 rounded-lg mb-2">
                        {{ grant.opportunity_title }}
                        <button @click="removeGrant(grant.id)" class="ml-2 text-red-500">X</button>
                    </li>
                </ul>
            </div>
        </div>

        <form @submit.prevent="handleResponse" class="text-2xl">
            <textarea
                v-model="form.message"
                placeholder="Which grants are attached to this conversation?"
                class="w-full p-3 rounded-lg border-none bg-gradient-to-r from-atechBlue to-atechBlue-dark tex-black text-l"
                rows="2"
            ></textarea>

            <button type="submit" class="send-button cursor-pointer rounded-lg border border-white bg-gradient-to-r from-atechBlue-light/85 to-[#dddddd] text-black p-4 mt-4 w-[20vw] mx-[38vw]">
                Send
            </button>
        </form>
    </div>
</template>

<script setup>
import { reactive, ref, nextTick } from 'vue';
import { useForm } from '@inertiajs/vue3';
import markdownit from 'markdown-it';
import markdownItLinkAttributes from 'markdown-it-link-attributes';

const md = markdownit({
  html: true,
  linkify: true,
  typographer: true
});

md.use(markdownItLinkAttributes, {
  attrs: {
    target: "_blank",
    rel: "noopener",
  },
});

const form = reactive(useForm({
    message: '',
    errors: {}
}));

const messages = ref([]);
const loading = ref(false);
const chatHistory = ref(null);

// Props for the grants and govgrants
const props = defineProps(['grants', 'govgrants']);

// Emit event when a grant is removed
const emit = defineEmits(['remove-grant']);

// Remove grant from the assistant's conversation
const removeGrant = (grantId) => {
    // Emit the remove-grant event to the parent component (Home.vue)
    emit('remove-grant', grantId);
};

// Scroll function
const scrollToBottom = () => {
    nextTick(() => {
        if (chatHistory.value) {
            chatHistory.value.scrollTop = chatHistory.value.scrollHeight;
        }
    });
};

// Handle form submission
const handleResponse = async () => {
    if (form.message.trim() === '') return;

    messages.value.push({ role: 'user', content: form.message });
    loading.value = true;

    const userMessage = form.message;
    form.message = '';

    scrollToBottom(); // Scroll after user message is added

    // Prepare body data
    const bodyData = {
        message: userMessage,
        history: messages.value, // Send the entire message history
        grants: props.grants, // Include the selected grants
    };

    const response = await fetch(route('ai.assistant'), {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(bodyData)
    });

    const reader = response.body.getReader();
    const decoder = new TextDecoder();
    let assistantMessage = '';
    let chunk;

    messages.value.push({ role: 'assistant', content: '' }); // Prepare to append the assistant message

    while (!(chunk = await reader.read()).done) {
        assistantMessage += decoder.decode(chunk.value, { stream: true });

        const assistantMessageIndex = messages.value.findLastIndex((message) => message.role === 'assistant');
        messages.value[assistantMessageIndex].content = assistantMessage;

        scrollToBottom();
    }

    loading.value = false;
};

// Render markdown
const renderMarkdown = (content) => {
    return md.render(content);
};
</script>

<style scoped>
.scrollbar-thin {
  scrollbar-width: thin;
}
.scrollbar-thumb-atechBlue {
  scrollbar-color: #2c3e50 black;
}

/* comprehensive link styling */

.ai-assistant a {
    padding-left: 1.25rem;
    padding-right: 1.25rem;
    margin-top: 1rem;
    background: linear-gradient(to top, #1f8eb9, #B8CFD6);
    box-shadow: 0 10px 15px rgba(0, 0, 0, 0.1);
    color: black;
    font-size: 1.125rem;
    border-radius: 0.5rem;
    text-decoration: none;
    display: inline-block;
}

.send-button {
    min-width: 100px;
}
</style>
