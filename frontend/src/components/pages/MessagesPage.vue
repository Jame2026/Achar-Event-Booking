<script setup>
const props = defineProps([
  'bindings',
  'filteredConversations',
  'activeConversation',
  'selectConversation',
  'sendMessage',
  'sendFiles',
  'sendLocation',
  'isSharingLocation',
  'saveDocument',
  'deleteMessage',
])
</script>

<template>
  <main class="messages-page">
    <section class="messages-layout">
      <aside class="messages-sidebar">
        <h2>Messages</h2>
        <input
          type="search"
          placeholder="Search conversations..."
          :value="props.bindings.conversationSearch.value"
          @input="props.bindings.conversationSearch.value = $event.target.value"
        />

        <div class="conversation-list">
          <article
            v-for="chat in props.filteredConversations"
            :key="chat.id"
            class="conversation-item"
            :class="{ active: chat.id === props.bindings.selectedConversationId.value }"
            @click="props.selectConversation(chat.id)"
          >
            <img :src="chat.image" :alt="chat.name" />
            <div class="conversation-text">
              <div>
                <h3>{{ chat.name }}</h3>
                <span>{{ chat.time }}</span>
              </div>
              <p>{{ chat.preview }}</p>
            </div>
          </article>
        </div>
      </aside>

      <section class="chat-panel">
        <header class="chat-header">
          <div class="chat-person">
            <img :src="props.activeConversation.image" :alt="props.activeConversation.name" />
            <div>
              <h3>{{ props.activeConversation.name }}</h3>
              <p>{{ props.activeConversation.online ? 'Online' : 'Offline' }}</p>
            </div>
          </div>
        </header>

        <div class="chat-stream">
          <span class="chat-day">Today</span>

          <div
            v-for="msg in props.activeConversation.messages"
            :key="msg.id"
            class="bubble-row"
            :class="msg.from === 'me' ? 'right' : 'left'"
          >
            <div class="bubble" :class="msg.from === 'me' ? 'accent' : ''">
              <span v-if="msg.text">{{ msg.text }}</span>
              <img v-if="msg.image" :src="msg.image" alt="Shared attachment" />
              <div v-if="msg.documentUrl" class="attachment-card doc-attachment">
                <a
                  class="doc-link"
                  :href="msg.documentUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  {{ msg.documentName || 'Open document' }}
                </a>
                <div class="doc-actions">
                  <button type="button" class="msg-action save" @click="props.saveDocument(msg)">Save</button>
                  <button type="button" class="msg-action delete" @click="props.deleteMessage(msg.id)">Delete</button>
                </div>
              </div>
              <div v-if="msg.locationUrl" class="attachment-card location-attachment">
                <div class="location-title">Location</div>
                <small v-if="msg.locationLabel">{{ msg.locationLabel }}</small>
                <div class="doc-actions">
                  <a
                    class="msg-action open"
                    :href="msg.locationUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    Open
                  </a>
                  <button type="button" class="msg-action delete" @click="props.deleteMessage(msg.id)">Delete</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <footer class="chat-composer">
          <label class="composer-icon composer-file">
            +
            <input
              type="file"
              class="composer-file-input"
              accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt"
              @change="props.sendFiles"
            />
          </label>
          <button type="button" class="composer-location" :disabled="props.isSharingLocation" @click="props.sendLocation">
            {{ props.isSharingLocation ? 'Locating...' : 'Location' }}
          </button>
          <input
            type="text"
            placeholder="Type your message..."
            :value="props.bindings.composerText.value"
            @input="props.bindings.composerText.value = $event.target.value"
            @keyup.enter="props.sendMessage"
          />
          <button type="button" class="composer-send" :disabled="!props.bindings.composerText.value.trim()" @click="props.sendMessage">Send</button>
        </footer>
      </section>
    </section>
  </main>
</template>
