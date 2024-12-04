<template>
    <!-- Show Assistant toggle -->
<div id="assistantContainer">

    <div class="ai-assistant" v-show="showAssistant">
        <div ref="chatHistory" class="chat-history">
            <div class="assistant-message ">
                <p>Hi! I'm the Grants AI Assistant, based on OpenAI's GPT-4o-mini model. I can help you with the grants in your search results. Click "Add to AI Chatbot" next to a grant to add it to our conversation.</p>
            </div>
            <div v-for="(message, index) in messages" :key="index" class="message-item mb-2 clear-both">
                <p v-if="message.role === 'user'" class="user-message">
                    {{ message.content }}
                </p>
                <div v-if="message.role === 'assistant'" class="assistant-message ">
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
                    <li v-for="(grant, index) in grants" :key="'grant-' + index" class="grant-item">
                        {{ grant.opportunity_title }} {{ grant.opportunityTitle }}
                        <button @click="removeGrant(grant.id)" class="ml-2 text-red-500">X</button>
                    </li>
                </ul>
            </div>
        </div>

        <form @submit.prevent="handleResponse" class="text-xl">
            <textarea
                v-model="form.message"
                placeholder="Which grants are attached to this conversation?"
                class="message-input"
                rows="2"
            ></textarea>
            <div class="button-group">
                <button type="submit" class="send-button ">
                    Send
                </button>
                <button @click="downloadConversation" class="download-button">
                    Download Chat
                </button>
            </div>
        </form>
    </div>
    <div class="flex items-center ">
        <button @click="showAssistant = !showAssistant" class="border-2  font-bold py-2 px-4 rounded">
            {{ showAssistant ? 'Hide' : 'Show' }} Assistant
        </button>
    </div>
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

const showAssistant = ref(true);

console.log('Grants:', props.grants);
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

const downloadConversation = () => {
    const conversation = messages.value.map((message) => `${message.role}: ${message.content}`).join('\n');
    const blob = new Blob([conversation], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    //put human readable timestamp to the second in the file name
    a.download = `grants-conversation-${new Date().toISOString().replace(/[-:.]/g, '').replace('T', '-').slice(0, 17)}.txt`;
    a.click();
    URL.revokeObjectURL(url);
};
</script>

<style scoped>

#assistantContainer {
    position: fixed;
    bottom: 10px;
    left: 10px;
    z-index: 1;
    border: 2px solid black;
    border-radius: 5px;
    background-color: #f4f1eb;
}

.ai-assistant {
    padding: 1rem;
    background-color: #f4f1eb;
    position: relative;
    z-index: 2;
    font-size: 1.125rem;
    max-width: 500px;
}

.chat-history {
    max-height: 69vh;
    overflow-y: auto;
    margin-bottom: 1rem;
    scrollbar-color: #2c3e50 white;
}

.assistant-message {
    color: black;
    background: white;
    border-radius: 5px;
    padding: 1rem;
    margin-right: 0.25rem;
    display: inline-block;
    max-width: 95%;
    float: left;
    margin-bottom: 0.5rem;
    text-align: left;
}

.user-message {
    color: black;
    background: white;
    border-radius: 5px;
    padding: 1rem;
    margin-left: 0.5rem;
    display: inline-block;
    max-width: 95%;
    float: right;
    margin-bottom: 0.5rem;
    text-align: right;
}

.loading-indicator {
    color: black;
    margin-top: 1rem;
    font-style: italic;
}

.grants-section {
    margin-bottom: 1rem;
}

.grants-list {
    margin-top: 0.5rem;
}

.grants-title {
    color: black;
    font-weight: bold;
}

.grant-item {
    color: black;
    display: flex;
    align-items: center;
    background: #fff;
    padding: 5px;
    border-radius: 0.5rem;
    margin-bottom: 0.5rem;
}

.remove-button {
    margin-left: 0.5rem;
    color: #ff0000;
}

.form {
    font-size: 1.25rem;
}

.message-input {
    width: 100%;
    padding: 0.75rem;
    border-radius: 5px;
    border: none;
    background: white;
    color: black;
    font-size: 1rem;
}

.button-group {
    display: flex;
    flex-direction: row;
    gap: 0.5rem;
}


.download-button, .send-button {
    cursor: pointer;
    border: 2px solid black;
    border-radius: 5px;
    color: black;
    padding: 1rem;
    width: 100%;
}
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

button {
    background-color: #f4f1eb;
}
</style>
