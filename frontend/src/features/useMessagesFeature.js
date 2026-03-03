import { computed, ref } from 'vue'
import { conversationsSeed } from './appData'

export function useMessagesFeature(currentPage) {
  const conversationSearch = ref('')
  const composerText = ref('')
  const isSharingLocation = ref(false)
  const conversations = ref(conversationsSeed.map((chat) => ({ ...chat, messages: [...chat.messages] })))
  const selectedConversationId = ref(conversations.value[0].id)

  const filteredConversations = computed(() => {
    const q = conversationSearch.value.trim().toLowerCase()
    if (!q) return conversations.value
    return conversations.value.filter(
      (chat) => chat.name.toLowerCase().includes(q) || chat.preview.toLowerCase().includes(q),
    )
  })

  const activeConversation = computed(() => {
    const active = conversations.value.find((item) => item.id === selectedConversationId.value)
    return active || conversations.value[0]
  })

  const recentConversations = computed(() => conversations.value.slice(0, 3))

  function goToMessages(targetVendor) {
    currentPage.value = 'messages'
    if (!targetVendor) return
    const match = conversations.value.find((chat) =>
      chat.name.toLowerCase().includes(targetVendor.toLowerCase()),
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

    target.messages.push({
      id: Date.now(),
      from: 'me',
      time: 'Just now',
      ...payload,
    })

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

  function sendMessage() {
    const text = composerText.value.trim()
    if (!text) return

    pushOutgoingMessage({
      text,
    })
    composerText.value = ''
  }

  function sendFiles(event) {
    const input = event?.target
    const file = input?.files?.[0]
    if (!file) return

    const objectUrl = URL.createObjectURL(file)
    const isImage = file.type.startsWith('image/')

    if (isImage) {
      pushOutgoingMessage({
        text: file.name,
        image: objectUrl,
        objectUrl,
      })
    } else {
      pushOutgoingMessage({
        text: 'Shared a document',
        documentName: file.name,
        documentUrl: objectUrl,
        objectUrl,
      })
    }

    input.value = ''
  }

  function sendLocation() {
    if (!navigator.geolocation || isSharingLocation.value) return

    isSharingLocation.value = true
    navigator.geolocation.getCurrentPosition(
      (position) => {
        const lat = Number(position.coords.latitude).toFixed(6)
        const lng = Number(position.coords.longitude).toFixed(6)
        pushOutgoingMessage({
          text: 'Shared location',
          locationLabel: `${lat}, ${lng}`,
          locationUrl: `https://www.google.com/maps?q=${lat},${lng}`,
        })
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
    const target = getSelectedConversation()
    if (!target) return

    const index = target.messages.findIndex((message) => message.id === messageId)
    if (index < 0) return

    const [removed] = target.messages.splice(index, 1)
    if (removed?.objectUrl) {
      URL.revokeObjectURL(removed.objectUrl)
    }

    const lastMessage = target.messages[target.messages.length - 1] || null
    target.preview = getMessagePreview(lastMessage)
    target.time = lastMessage?.time || ''
  }

  return {
    conversationSearch,
    composerText,
    isSharingLocation,
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
  }
}
