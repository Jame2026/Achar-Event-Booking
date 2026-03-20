
import { computed, ref, watch } from 'vue'
import { apiGet, apiPost } from './apiClient'

const DEFAULT_AVATAR = 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=180&q=80'

function isVendorUser(user) {
  const role = String(user?.role || '').trim().toLowerCase()
  return role === 'vendor' || role === 'admin'
}

export function useVendorMessagesFeature(currentPage, loggedInUser, notice, vendorDashboardTab = null) {
  const conversationSearch = ref('')
  const composerText = ref('')
  const isSharingLocation = ref(false)
  const isLoadingMessages = ref(false)
  const messagesError = ref('')
  const conversations = ref([])
  const selectedConversationId = ref(conversations.value[0]?.id || null)
  const vendorMode = computed(() => isVendorUser(loggedInUser?.value))
  const userMode = computed(() => !isVendorUser(loggedInUser?.value) && Boolean(customerEmail()))
  const dashboardTab = computed(() => {
    if (!vendorDashboardTab) return null
    if (typeof vendorDashboardTab === 'string') return vendorDashboardTab
    if (typeof vendorDashboardTab === 'object' && 'value' in vendorDashboardTab) return vendorDashboardTab.value
    return null
  })

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
      vendorEmail: '',
      vendorPhone: '',
      vendorLocation: '',
      customerEmail: '',
      customerPhone: '',
      customerLocation: '',
    }
  })

  const recentConversations = computed(() => conversations.value.slice(0, 3))
  let refreshTimer = null

  function vendorUserId() {
    const id = Number(loggedInUser?.value?.id || 0)
    return Number.isFinite(id) && id > 0 ? id : null
  }

  function customerEmail() {
    const email = String(loggedInUser?.value?.email || '').trim().toLowerCase()
    if (email) return email
    const stored = String(localStorage.getItem('achar_customer_email') || '').trim().toLowerCase()
    return stored || null
  }

  function normalizeTargetVendor(target) {
    if (!target) return {}
    if (typeof target === 'string') return { vendorName: target }
    if (typeof target === 'object') {
      const eventIdValue = Number(target.eventId || target.event_id || target.backingEventId || 0)
      return {
        vendorName: String(target.vendorName || target.name || '').trim(),
        vendorEmail: String(target.vendorEmail || target.email || '').trim(),
        vendorId: Number.isFinite(Number(target.vendorId || target.id)) ? Number(target.vendorId || target.id) : null,
        serviceName: String(target.serviceName || '').trim(),
        eventId: Number.isFinite(eventIdValue) && eventIdValue > 0 ? eventIdValue : null,
      }
    }
    return {}
  }

  async function ensureUserConversation(targetVendor) {
    const email = customerEmail()
    if (!email) return null

    await loadUserConversations({ preserveSelection: true })
    if (!targetVendor) return null

    const { vendorName, vendorEmail, vendorId, serviceName, eventId } = normalizeTargetVendor(targetVendor)
    const normalizedName = vendorName.toLowerCase()
    let match = normalizedName
      ? conversations.value.find((chat) => chat.name.toLowerCase().includes(normalizedName))
      : null

    if (match) {
      selectedConversationId.value = match.id
      return match
    }

    if (!vendorId && !vendorEmail && !eventId && !vendorName) {
      return null
    }

    try {
      const result = await apiPost('user/chats', {
        vendor_user_id: vendorId || undefined,
        vendor_email: vendorEmail || undefined,
        vendor_name: vendorName || undefined,
        event_id: eventId || undefined,
        customer_email: email,
        customer_name: String(loggedInUser?.value?.name || '').trim() || undefined,
        service_name: serviceName || undefined,
      })
      const created = result?.data
      if (created?.id) {
        await loadUserConversations({ preserveSelection: true })
        const createdMatch = conversations.value.find((chat) => chat.id === created.id)
        if (createdMatch) selectedConversationId.value = createdMatch.id
        return createdMatch || null
      }
    } catch (error) {
      messagesError.value = error?.message || 'Could not start a new conversation.'
      if (notice && 'value' in notice) notice.value = 'Could not start a new conversation.'
    }

    return null
  }

  function mapVendorConversation(chat) {
    const rows = Array.isArray(chat?.messages) ? chat.messages : []
    const lastRow = rows[rows.length - 1]
    return {
      id: chat.id,
      name: String(chat.customer_name || chat.customer_email || `Customer #${chat.id}`),
      preview: rows.length ? String(lastRow?.body || '') : '',
      time: rows.length ? String(lastRow?.time_label || '') : '',
      online: false,
      image: String(chat.customer_image_url || DEFAULT_AVATAR),
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
      customerPhone: String(chat.customer_phone || ''),
      customerLocation: String(chat.customer_location || ''),
    }
  }

  function mapUserConversation(chat) {
    const rows = Array.isArray(chat?.messages) ? chat.messages : []
    const lastRow = rows[rows.length - 1]
    return {
      id: chat.id,
      name: String(chat.vendor_name || chat.vendor_email || `Vendor #${chat.id}`),
      preview: rows.length ? String(lastRow?.body || '') : '',
      time: rows.length ? String(lastRow?.time_label || '') : '',
      online: false,
      image: String(chat.vendor_image_url || DEFAULT_AVATAR),
      messages: rows.map((message) => ({
        id: message.id,
        from: message.sender_role === 'customer' ? 'me' : 'them',
        text: String(message.body || ''),
        time: String(message.time_label || 'Just now'),
      })),
      serviceName: String(chat.service_name || 'Service Booking'),
      bookingId: chat.booking_id,
      bookingStatus: String(chat.booking_status || ''),
      vendorId: chat.vendor_id,
      vendorEmail: String(chat.vendor_email || ''),
      vendorPhone: String(chat.vendor_phone || ''),
      vendorLocation: String(chat.vendor_location || ''),
    }
  }

  async function loadVendorConversations(options = {}) {
    const { preserveSelection = true } = options
    if (!vendorMode.value) return
    const vendorId = vendorUserId()
    const vendorEmail = String(loggedInUser?.value?.email || '').trim()
    const vendorName = String(loggedInUser?.value?.name || '').trim()
    if (!vendorId && !vendorEmail) {
      messagesError.value = 'Vendor account id is missing.'
      conversations.value = []
      selectedConversationId.value = null
      return
    }

    isLoadingMessages.value = true
    messagesError.value = ''
    try {
      const result = await apiGet('vendor/chats', {
        vendor_user_id: vendorId || undefined,
        vendor_email: vendorEmail || undefined,
        vendor_name: vendorName || undefined,
      })
      const mapped = (Array.isArray(result?.data) ? result.data : []).map(mapVendorConversation)
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

  async function loadUserConversations(options = {}) {
    const { preserveSelection = true } = options
    if (!userMode.value) return
    const email = customerEmail()
    if (!email) {
      messagesError.value = 'Customer email is missing.'
      conversations.value = []
      selectedConversationId.value = null
      return
    }

    isLoadingMessages.value = true
    messagesError.value = ''
    try {
      const result = await apiGet('user/chats', { customer_email: email })
      const mapped = (Array.isArray(result?.data) ? result.data : []).map(mapUserConversation)
      const previousId = selectedConversationId.value
      conversations.value = mapped
      if (!mapped.length) selectedConversationId.value = null
      else if (preserveSelection && previousId && mapped.some((chat) => chat.id === previousId)) selectedConversationId.value = previousId
      else selectedConversationId.value = mapped[0].id
    } catch (error) {
      messagesError.value = error?.message || 'Could not load customer conversations.'
      if (notice && 'value' in notice) notice.value = 'Could not load customer chat conversations.'
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

    if (userMode.value) {
      await ensureUserConversation(targetVendor)
      return
    }

    if (!targetVendor) return
    const match = conversations.value.find((chat) =>
      chat.name.toLowerCase().includes(String(targetVendor).toLowerCase()),
    )
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
    target.preview = getMessagePreview(payload) || 'Shared an attachment'
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
    if (!text) {
      messagesError.value = 'Message cannot be empty.'
      if (notice && 'value' in notice) notice.value = 'Message cannot be empty.'
      return
    }

    messagesError.value = ''

    if (vendorMode.value) {
      const conversation = getSelectedConversation()
      const vendorId = vendorUserId()
      if (!conversation) {
        messagesError.value = 'Please select a conversation before sending a message.'
        if (notice && 'value' in notice) notice.value = 'Please select a conversation before sending a message.'
        return
      }
      if (!vendorId) {
        messagesError.value = 'Vendor account id is missing.'
        if (notice && 'value' in notice) notice.value = 'Vendor account id is missing.'
        return
      }

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

    if (userMode.value) {
      const conversation = getSelectedConversation()
      const email = customerEmail()
      if (!conversation) {
        messagesError.value = 'Please select a conversation before sending a message.'
        if (notice && 'value' in notice) notice.value = 'Please select a conversation before sending a message.'
        return
      }
      if (!email) {
        messagesError.value = 'Customer email is missing.'
        if (notice && 'value' in notice) notice.value = 'Customer email is missing.'
        return
      }

      try {
        const result = await apiPost(`user/chats/${conversation.id}/messages`, {
          customer_email: email,
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
        if (notice && 'value' in notice) notice.value = 'Could not send customer chat message.'
      }
      return
    }

    pushOutgoingMessage({ text })
    composerText.value = ''
  }

  function sendFiles(event) {
    if (vendorMode.value || userMode.value) {
      if (notice && 'value' in notice) notice.value = 'Attachment upload is not enabled yet for chat.'
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
    if (vendorMode.value || userMode.value) {
      if (notice && 'value' in notice) notice.value = 'Location share is not enabled yet for chat.'
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
    if (vendorMode.value || userMode.value) {
      if (notice && 'value' in notice) notice.value = 'Message delete is not enabled yet for chat.'
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
    [() => currentPage.value, () => vendorMode.value, () => userMode.value, () => dashboardTab.value],
    ([page, isVendor, isUser, tab]) => {
      const inMessagesPage = page === 'messages'
      const inVendorDashboardMessages = page === 'dashboard' && isVendor && tab === 'messages'
      const shouldSync = (inMessagesPage || inVendorDashboardMessages) && (isVendor || isUser)

      if (shouldSync) {
        if (isVendor) void loadVendorConversations({ preserveSelection: true })
        if (isUser && inMessagesPage) void loadUserConversations({ preserveSelection: true })
        if (!refreshTimer) {
          refreshTimer = setInterval(() => {
            if (isLoadingMessages.value) return
            const pageNow = currentPage.value
            const tabNow = dashboardTab.value
            const inMessagesNow = pageNow === 'messages'
            const inVendorDashboardNow = pageNow === 'dashboard' && vendorMode.value && tabNow === 'messages'
            if (!inMessagesNow && !inVendorDashboardNow) return
            if (vendorMode.value) void loadVendorConversations({ preserveSelection: true })
            if (userMode.value && inMessagesNow) void loadUserConversations({ preserveSelection: true })
          }, 10000)
        }
      } else if (refreshTimer) {
        clearInterval(refreshTimer)
        refreshTimer = null
      }
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
    loadUserConversations,
  }
}

