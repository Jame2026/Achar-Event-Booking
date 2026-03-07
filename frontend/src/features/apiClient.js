const DEFAULT_API_TIMEOUT_MS = 15000

function buildApiPath(path, query = {}) {
  if (typeof path !== 'string' || !path.trim()) {
    throw new Error(`Invalid API path: ${String(path)}`)
  }

  const normalizedPath = path.replace(/^\/+/, '')
  const params = new URLSearchParams()

  Object.entries(query).forEach(([key, value]) => {
    if (value !== undefined && value !== null && value !== '') {
      params.set(key, String(value))
    }
  })

  const queryString = params.toString()
  return `/api/${normalizedPath}${queryString ? `?${queryString}` : ''}`
}

async function readErrorMessage(response) {
  const contentType = response.headers.get('content-type') || ''

  if (contentType.includes('application/json')) {
    const errorBody = await response.json().catch(() => ({}))
    const validationErrors = errorBody?.errors
    if (validationErrors && typeof validationErrors === 'object') {
      const firstMessage = Object.values(validationErrors).flat().find(Boolean)
      if (firstMessage) return String(firstMessage)
    }
    if (errorBody?.message) return String(errorBody.message)
  } else {
    const text = await response.text().catch(() => '')
    if (text.trim()) return text.trim()
  }

  return `Request failed (${response.status})`
}

async function readJsonResponse(response, requestPath) {
  const contentType = response.headers.get('content-type') || ''

  if (!contentType.includes('application/json')) {
    const text = await response.text().catch(() => '')
    const sample = text.trim().slice(0, 120)
    const responseUrl = response.url || 'unknown URL'
    const statusLabel = `${response.status} ${response.statusText}`.trim()

    throw new Error(
      sample.startsWith('<')
        ? `API returned HTML instead of JSON for ${requestPath}; response URL was ${responseUrl} (${statusLabel}).`
        : `API returned an unexpected response format for ${requestPath}; response URL was ${responseUrl} (${statusLabel}, content-type: ${contentType || 'unknown'}).`,
    )
  }

  return response.json()
}

async function requestApi(method, path, { payload, query } = {}) {
  const requestPath = buildApiPath(path, query)
  const isFormData = payload instanceof FormData
  const controller = new AbortController()
  const timeoutId = setTimeout(() => controller.abort(), DEFAULT_API_TIMEOUT_MS)
  const headers = {
    Accept: 'application/json',
  }

  if (!isFormData && payload !== undefined) {
    headers['Content-Type'] = 'application/json'
  }

  let response
  try {
    response = await fetch(requestPath, {
      method,
      headers,
      body: payload === undefined ? undefined : isFormData ? payload : JSON.stringify(payload),
      signal: controller.signal,
    })
  } catch (error) {
    if (error?.name === 'AbortError') {
      throw new Error(`Request timeout after ${Math.floor(DEFAULT_API_TIMEOUT_MS / 1000)}s for ${requestPath}.`)
    }
    throw error
  } finally {
    clearTimeout(timeoutId)
  }

  if (!response.ok) {
    throw new Error(await readErrorMessage(response))
  }

  if (method === 'DELETE') {
    return null
  }

  return readJsonResponse(response, requestPath)
}

export async function apiGet(path, query = {}) {
  return requestApi('GET', path, { query })
}

export async function apiPost(path, payload) {
  return requestApi('POST', path, { payload })
}

export async function apiPatch(path, payload = {}) {
  return requestApi('PATCH', path, { payload })
}

export async function apiDelete(path, query = {}) {
  return requestApi('DELETE', path, { query })
}
