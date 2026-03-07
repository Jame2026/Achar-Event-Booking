
import { computed, ref, watch } from 'vue'
import { apiGet, apiPost } from './apiClient'
import { conversationsSeed } from './appData'

const DEFAULT_AVATAR = 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=180&q=80'

function isVendorUser(user) {
  const role = String(user?.role || '').trim().toLowerCase()
  return role === 'vendor' || role === 'admin'
}

export function useVendorMessagesFeature(currentPage, loggedInUser, notice) {
  const conversationSearch = ref('')
  const composerText = ref('')
  const isSharingLocation = ref(false)
  const isLoadingMessages = ref(false)
  const messagesError = ref('')
  const conversations = ref(conversationsSeed.map((chat) => ({ ...chat, messages: [...chat.messages] })))
  const selectedConversationId = ref(conversations.value[0]?.id || null)
  const vendorMode = computed(() => isVendorUser(loggedInUser?.value))

  const filteredConversations = computed(() => {
    const q = conversationSearch.value.trim().toLowerCase()
    if (!q) return conversations.value
    return conversations.value.filter((chat) => chat.name.toLowerCase().includes(q) || chat.preview.toLowerCase().includes(q))
  })

  const activeConversation = computed(() => {
    const active = conversations.value.find((item) => item.id === selectedConversationId.value)
    if (active) return active
    return {
      id: null,
      name: 'No conversation selected',
      preview: '',
      time: '',
      online: false,
      image: DEFAULT_AVATAR,
      messages: [],
      serviceName: '',
      bookingId: null,
      bookingStatus: '',
    }
  })

  const recentConversations = computed(() => conversations.value.slice(0, 3))

  function vendorUserId() {
    const id = Number(loggedInUser?.value?.id || 0)
    return Number.isFinite(id) && id > 0 ? id : null
  }

  function mapApiConversation(chat) {
    const rows = Array.isArray(chat?.messages) ? chat.messages : []
    return {
      id: chat.id,
      name: String(chat.customer_name || chat.customer_email || `Customer #${chat.id}`),
      preview: String(chat.preview || 'No messages yet.'),
      time: rows.length ? String(rows[rows.length - 1].time_label || 'Just now') : '',
      online: false,
      image: DEFAULT_AVATAR,
      messages: rows.map((message) => ({
        id: message.id,
        from: message.sender_role === 'vendor' ? 'me' : 'them',
        text: String(message.body || ''),
        time: String(message.time_label || 'Just now'),
      })),
      serviceName: String(chat.service_name || 'Service Booking'),
      bookingId: chat.booking_id,
      bookingStatus: String(chat.booking_status || ''),
      customerEmail: String(chat.customer_email || ''),
    }
  }

  async function loadVendorConversations(options = {}) {
    const { preserveSelection = true } = options
    if (!vendorMode.value) return
    const vendorId = vendorUserId()
    if (!vendorId) {
      messagesError.value = 'Vendor account id is missing.'
      conversations.value = []
      selectedConversationId.value = null
      return
    }

    isLoadingMessages.value = true
    messagesError.value = ''
    try {
      const result = await apiGet('vendor/chats', { vendor_user_id: vendorId })
      const mapped = (Array.isArray(result?.data) ? result.data : []).map(mapApiConversation)
      const previousId = selectedConversationId.value
      conversations.value = mapped
      if (!mapped.length) selectedConversationId.value = null
      else if (preserveSelection && previousId && mapped.some((chat) => chat.id === previousId)) selectedConversationId.value = previousId
      else selectedConversationId.value = mapped[0].id
    } catch (error) {
      messagesError.value = error?.message || 'Could not load vendor conversations.'
      if (notice && 'value' in notice) notice.value = 'Could not load vendor chat conversations.'
    } finally {
      isLoadingMessages.value = false
    }
  }

  async function goToMessages(targetVendor) {
    currentPage.value = 'messages'
    if (vendorMode.value) {
      if (!conversations.value.length) await loadVendorConversations({ preserveSelection: true })
      return
    }

    if (!targetVendor) return
    const match = conversations.value.find((chat) => chat.name.toLowerCase().includes(String(targetVendor).toLowerCase()))
    if (match) selectedConversationId.value = match.id
  }

  function selectConversation(id) {
    selectedConversationId.value = id
  }

  function getSelectedConversation() {
    return conversations.value.find((item) => item.id === selectedConversationId.value) || null
  }

  function pushOutgoingMessage(payload) {
    const target = getSelectedConversation()
    if (!target) return
    target.messages.push({ id: Date.now(), from: 'me', time: 'Just now', ...payload })
    target.preview = payload.text || payload.documentName || 'Shared an attachment'
    target.time = 'Just now'
  }

  function getMessagePreview(message) {
    if (!message) return ''
    if (message.text) return message.text
    if (message.documentName) return message.documentName
    if (message.locationLabel) return 'Shared location'
    if (message.image) return 'Shared an image'
    return 'Shared an attachment'
  }

  function bumpConversation(conversationId) {
    const index = conversations.value.findIndex((item) => item.id === conversationId)
    if (index <= 0) return
    const [row] = conversations.value.splice(index, 1)
    conversations.value.unshift(row)
  }

  async function sendMessage() {
    const text = composerText.value.trim()
    if (!text) return

    if (vendorMode.value) {
      const conversation = getSelectedConversation()
      const vendorId = vendorUserId()
      if (!conversation || !vendorId) return

      try {
        const result = await apiPost(`vendor/chats/${conversation.id}/messages`, {
          vendor_user_id: vendorId,
          body: text,
        })
        const created = result?.data || {}
        conversation.messages.push({
          id: created.id || Date.now(),
          from: 'me',
          text,
          time: String(created.time_label || 'Just now'),
        })
        conversation.preview = text
        conversation.time = String(created.time_label || 'Just now')
        bumpConversation(conversation.id)
        composerText.value = ''
      } catch (error) {
        messagesError.value = error?.message || 'Could not send message.'
        if (notice && 'value' in notice) notice.value = 'Could not send vendor chat message.'
      }
      return
    }

    pushOutgoingMessage({ text })
    composerText.value = ''
  }

  function sendFiles(event) {
    if (vendorMode.value) {
      if (notice && 'value' in notice) notice.value = 'Attachment upload is not enabled yet for vendor chat.'
      if (event?.target) event.target.value = ''
      return
    }

    const input = event?.target
    const file = input?.files?.[0]
    if (!file) return
    const objectUrl = URL.createObjectURL(file)
    const isImage = file.type.startsWith('image/')

    if (isImage) pushOutgoingMessage({ text: file.name, image: objectUrl, objectUrl })
    else pushOutgoingMessage({ text: 'Shared a document', documentName: file.name, documentUrl: objectUrl, objectUrl })
    input.value = ''
  }

  function sendLocation() {
    if (vendorMode.value) {
      if (notice && 'value' in notice) notice.value = 'Location share is not enabled yet for vendor chat.'
      return
    }

    if (!navigator.geolocation || isSharingLocation.value) return
    isSharingLocation.value = true
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = Number(position.coords.latitude).toFixed(6)
        const lng = Number(position.coords.longitude).toFixed(6)
        pushOutgoingMessage({ text: 'Shared location', locationLabel: `${lat}, ${lng}`, locationUrl: `https://www.google.com/maps?q=${lat},${lng}` })
        isSharingLocation.value = false
      },
      () => {
        isSharingLocation.value = false
      },
      { enableHighAccuracy: true, timeout: 10000 },
    )
  }

  function saveDocument(message) {
    if (!message?.documentUrl) return
    const link = document.createElement('a')
    link.href = message.documentUrl
    link.download = message.documentName || 'attachment'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }

  function deleteMessage(messageId) {
    if (vendorMode.value) {
      if (notice && 'value' in notice) notice.value = 'Message delete is not enabled yet for vendor chat.'
      return
    }

    const target = getSelectedConversation()
    if (!target) return
    const index = target.messages.findIndex((message) => message.id === messageId)
    if (index < 0) return
    const [removed] = target.messages.splice(index, 1)
    if (removed?.objectUrl) URL.revokeObjectURL(removed.objectUrl)
    const lastMessage = target.messages[target.messages.length - 1] || null
    target.preview = getMessagePreview(lastMessage)
    target.time = lastMessage?.time || ''
  }

  watch(
    () => currentPage.value,
    (page) => {
      if (page === 'messages' && vendorMode.value) void loadVendorConversations({ preserveSelection: true })
    },
  )

  return {
    conversationSearch,
    composerText,
    isSharingLocation,
    isLoadingMessages,
    messagesError,
    conversations,
    selectedConversationId,
    filteredConversations,
    activeConversation,
    recentConversations,
    goToMessages,
    selectConversation,
    sendMessage,
    sendFiles,
    sendLocation,
    saveDocument,
    deleteMessage,
    loadVendorConversations,
  }
}

