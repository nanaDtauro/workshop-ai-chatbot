<script setup lang="ts">
import { ref } from 'vue'
import { Head } from '@inertiajs/vue3';
import axios from 'axios'
import Message from '@/components/Message.vue'

const props = defineProps<{
    conversations?: array;
    role?: string;
}>();

const conversations = ref(props.conversations || [])
const messages = ref([])
const userInput = ref('')
const isLoading = ref(false)
const conversationId = ref(undefined)

const addMessage = (role, content, sources = []) => {
    messages.value.push({ role, content, sources })
}

const addMessageResponse = (role, response) => {
    messages.value.push({
        ...{role: role},
        ...response
    })
}

const sendMessage = async () => {
    const msg = userInput.value.trim()
    if (!msg) return
    addMessage('user', msg)
    userInput.value = ''
    isLoading.value = true
    try {
        const response = await axios.post('/chat', {
            message: msg,
            useAgent: 1,
            conversationId: conversationId.value
        })
        addMessageResponse('agent', response.data);
        if (response.data.conversationId && !conversationId.value) {
            conversationId.value = response.data.conversationId
            conversations.value.unshift({
                id: response.data.conversationId,
                title: msg.length > 150 ? msg.substring(0, 150) : msg
            });
        }
    } catch (err) {
        addMessage('agent', `Error: ${err.response?.data?.error || err.message}`)
    } finally {
        isLoading.value = false
    }
}

const newConversation = () => {
    messages.value = []
    conversationId.value = undefined;
}

const selectConversation = (id) => {
    if (conversationId.value !== id) {
        conversationId.value = id;
        loadConversationMessages();
    }
}

const loadConversationMessages = async () => {
    isLoading.value = true
    messages.value = []
    try {
        const response = await axios.get('/api/chat/messages', {
            params: {
                conversationId: conversationId.value
            }
        })
        if (response?.data) {
            for (const message of response.data) {
                addMessageResponse(message.role, message)
            }
        }
    } catch (err) {
        addMessage('agent', `Error: ${err.response?.data?.error || err.message}`)
    } finally {
        isLoading.value = false
    }
}

const deleteConversation = async (id, idx) => {
    isLoading.value = true
    messages.value = []
    try {
        const response = await axios.delete('/api/chat/conversations/'+id)
        if (response?.data) {
            conversations.value.splice(idx, 1);
        }
    } catch (err) {
        addMessage('agent', `Error: ${err.response?.data?.error || err.message}`)
    } finally {
        isLoading.value = false
    }
}

</script>

<template>
    <Head title="Chatbot"/>
    <div class="w-screen h-screen bg-black pb-17">
        <div class="grid grid-cols-3">
            <div class="conversation flex flex-col gap-4 p-4 border-r-2" style="height: calc(100vh - 68px);">
                <div class="flex justify-between items-center">
                    <h3 class="font-semibold">Conversations</h3>
                    <button
                        type="button"
                        class="otl oto otq otv oub ouc oud ouf ouh oui ouj oul oum ovn text-sm px-3 py-1.5 text-white cursor-pointer bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 disabled:bg-gray-500 disabled:cursor-not-allowed rounded"
                        @click.stop="newConversation"
                    >
                        New
                    </button>
                </div>
                <ul v-if="conversations.length">
                    <li v-for="(conversation, idx) in conversations"
                        :key="conversation.id"
                        :class="[
                            'p-1 rounded cursor-pointer text-gray-400 hover:text-gray-300',
                            {
                                'text-white font-semibold hover:text-gray-100': conversation.id === conversationId
                            }
                        ]"
                        @click.stop="selectConversation(conversation.id)"
                    >
                        <div class="flex justify-between items-center">
                            <span>{{conversation.title}}</span>
                            <button
                                type="button"
                                class="otl oto otq otv oub ouc oud ouf ouh oui ouj oul oum ovn text-sm px-3 py-1.5 text-white cursor-pointer bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 disabled:bg-gray-500 disabled:cursor-not-allowed rounded"
                                @click.stop="deleteConversation(conversation.id, idx)"
                            >
                                Delete
                            </button>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="messages col-span-2 w-full overflow-y-auto p-2" style="height: calc(100vh - 68px);">
                <Message
                    v-for="(message, i) in messages"
                    :key="i"
                    :message="message"
                    :role="role"
                />
                <div v-if="messages.length === 0" class="empty">
                    Start asking questions to your <strong>{{ role || 'agent'}}</strong>!
                </div>
                <div v-if="isLoading" class="loading bg-indigo-500/30 p-1 pl-4 flex items-center rounded">
                    <img src="https://freesvg.org/img/1544764567.png" class="mr-3 size-5 animate-spin"/>
                    <span class="text-sm">Thinking...</span>
                </div>
            </div>
        </div>
        <div class="input-area fixed w-full p-4 bottom-0 bg-stone-800 flex items-center justify-center gap-4">
            <input
                v-model="userInput"
                @keyup.enter="sendMessage"
                class="block min-w-0 w-full grow py-1.5 pr-3 p-2 text-base text-stone-200 placeholder:text-stone-400 focus:outline-none sm:text-sm/6 dark:bg-stone-900 dark:text-white dark:placeholder:text-gray-500"
                placeholder="Ask something..."
                :disabled="isLoading"
            />
            <button
                type="button"
                class="otl oto otq otv oub ouc oud ouf ouh oui ouj oul oum ovn text-sm px-3 py-1.5 text-white cursor-pointer bg-indigo-600 hover:bg-indigo-700 focus:ring-4 focus:ring-indigo-300 disabled:bg-gray-500 disabled:cursor-not-allowed rounded"
                @click.stop="sendMessage"
                :disabled="isLoading || !userInput.trim()"
            >
                Ask
            </button>
        </div>
    </div>
</template>