<script setup>
import { computed } from 'vue'
import { useLanguageCopy } from '../../features/language'

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
  'isLoadingMessages',
  'messagesError',
])

const copyByLanguage = {
  en: {
    messages: 'Messages',
    searchPlaceholder: 'Search conversations...',
    loading: 'Loading conversations...',
    noConversations: 'No conversations yet.',
    online: 'Online',
    offline: 'Offline',
    today: 'Today',
    openDocument: 'Open document',
    save: 'Save',
    delete: 'Delete',
    location: 'Location',
    open: 'Open',
    locating: 'Locating...',
    locationBtn: 'Location',
    typeMessage: 'Type your message...',
    send: 'Send',
  },
  km: {
    messages: 'សារ',
    searchPlaceholder: 'ស្វែងរកការសន្ទនា...',
    loading: 'កំពុងផ្ទុកការសន្ទនា...',
    noConversations: 'មិនទាន់មានការសន្ទនាទេ។',
    online: 'អនឡាញ',
    offline: 'ក្រៅបណ្ដាញ',
    today: 'ថ្ងៃនេះ',
    openDocument: 'បើកឯកសារ',
    save: 'រក្សាទុក',
    delete: 'លុប',
    location: 'ទីតាំង',
    open: 'បើក',
    locating: 'កំពុងស្វែងរក...',
    locationBtn: 'ទីតាំង',
    typeMessage: 'វាយសាររបស់អ្នក...',
    send: 'ផ្ញើ',
  },
  zh: {
    messages: '消息',
    searchPlaceholder: '搜索会话...',
    loading: '正在加载会话...',
    noConversations: '暂无会话。',
    online: '在线',
    offline: '离线',
    today: '今天',
    openDocument: '打开文档',
    save: '保存',
    delete: '删除',
    location: '位置',
    open: '打开',
    locating: '定位中...',
    locationBtn: '位置',
    typeMessage: '输入您的消息...',
    send: '发送',
  },
}

const { uiText } = useLanguageCopy(copyByLanguage)

function normalizeInfo(value) {
  const trimmed = String(value || '').trim()
  return trimmed !== '' ? trimmed : ''
}

function buildContactLine(conversation) {
  if (!conversation) return ''
  const vendorParts = [conversation.vendorEmail, conversation.vendorPhone, conversation.vendorLocation]
    .map(normalizeInfo)
    .filter(Boolean)
  if (vendorParts.length) return vendorParts.join(' - ')
  const customerParts = [conversation.customerEmail, conversation.customerPhone, conversation.customerLocation]
    .map(normalizeInfo)
    .filter(Boolean)
  if (customerParts.length) return customerParts.join(' - ')
  return ''
}

const hasActiveConversation = computed(() => {
  const active = props.activeConversation
  return Boolean(active && active.id)
})

const primaryLine = computed(() => {
  const active = props.activeConversation || {}
  const serviceName = normalizeInfo(active.serviceName)
  const bookingId = active.bookingId
  const hasBookingId = bookingId !== null && bookingId !== undefined && String(bookingId).trim() !== ''
  if (serviceName && hasBookingId) return `Booking #${bookingId} - ${serviceName}`
  if (serviceName) return serviceName
  return ''
})

const contactLine = computed(() => buildContactLine(props.activeConversation))
</script>

<template>
  <main class="messages-page">
    <section class="messages-layout">
      <aside class="messages-sidebar">
        <h2>{{ uiText.messages }}</h2>
        <input
          type="search"
          :placeholder="uiText.searchPlaceholder"
          :value="props.bindings.conversationSearch.value"
          @input="props.bindings.conversationSearch.value = $event.target.value"
        />

        <p v-if="props.messagesError" class="notice">{{ props.messagesError }}</p>
        <p v-else-if="props.isLoadingMessages" class="notice">{{ uiText.loading }}</p>

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
          <p v-if="!props.isLoadingMessages && props.filteredConversations.length === 0" class="notice">
            {{ uiText.noConversations }}
          </p>
        </div>
      </aside>

      <section class="chat-panel">
        <header v-if="hasActiveConversation" class="chat-header">
          <div class="chat-person">
            <img :src="props.activeConversation.image" :alt="props.activeConversation.name" />
            <div>
              <h3>{{ props.activeConversation.name }}</h3>
              <p v-if="primaryLine">{{ primaryLine }}</p>
              <p v-if="contactLine" class="chat-subline">{{ contactLine }}</p>
            </div>
          </div>
        </header>

        <div v-if="hasActiveConversation" class="chat-stream">
          <div
            v-for="msg in props.activeConversation.messages"
            :key="msg.id"
            class="bubble-row"
            :class="msg.from === 'me' ? 'right' : 'left'"
          >
            <div class="bubble" :class="msg.from === 'me' ? 'accent' : ''">
              <span v-if="msg.text">{{ msg.text }}</span>
              <img
                v-if="msg.image"
                class="chat-image"
                :src="msg.image"
                alt="Shared attachment"
                loading="lazy"
              />
              <div v-if="msg.documentUrl" class="attachment-card doc-attachment">
                <a
                  class="doc-link"
                  :href="msg.documentUrl"
                  target="_blank"
                  rel="noopener noreferrer"
                >
                  {{ msg.documentName || uiText.openDocument }}
                </a>
                <div class="doc-actions">
                  <button type="button" class="msg-action save" @click="props.saveDocument(msg)">{{ uiText.save }}</button>
                  <button type="button" class="msg-action delete" @click="props.deleteMessage(msg.id)">{{ uiText.delete }}</button>
                </div>
              </div>
              <div v-if="msg.locationUrl" class="attachment-card location-attachment">
                <div class="location-title">{{ uiText.location }}</div>
                <small v-if="msg.locationLabel">{{ msg.locationLabel }}</small>
                <div class="doc-actions">
                  <a
                    class="msg-action open"
                    :href="msg.locationUrl"
                    target="_blank"
                    rel="noopener noreferrer"
                  >
                    {{ uiText.open }}
                  </a>
                  <button type="button" class="msg-action delete" @click="props.deleteMessage(msg.id)">{{ uiText.delete }}</button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <footer v-if="hasActiveConversation" class="chat-composer">
          <label class="composer-icon composer-file">
            +
            <input
              type="file"
              class="composer-file-input"
              accept="image/*"
              @change="props.sendFiles"
            />
          </label>
          <button type="button" class="composer-location" :disabled="props.isSharingLocation" @click="props.sendLocation">
            {{ props.isSharingLocation ? uiText.locating : uiText.locationBtn }}
          </button>
          <input
            type="text"
            :placeholder="uiText.typeMessage"
            :value="props.bindings.composerText.value"
            @input="props.bindings.composerText.value = $event.target.value"
            @keyup.enter="props.sendMessage"
          />
          <button type="button" class="composer-send" :disabled="!props.bindings.composerText.value.trim()" @click="props.sendMessage">{{ uiText.send }}</button>
        </footer>
      </section>
    </section>
  </main>
</template>
