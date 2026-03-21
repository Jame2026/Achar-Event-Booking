export function normalizePackageServices(packages = []) {
  if (!Array.isArray(packages)) return []

  return packages
    .map((pkg) => ({
      ...(pkg && typeof pkg === 'object' ? pkg : {}),
      name: String(pkg?.name || '').trim(),
      price: Number(pkg?.price || 0),
      details: String(pkg?.details ?? pkg?.description ?? '').trim(),
    }))
    .filter((pkg) => pkg.name || pkg.details || pkg.price > 0)
}

export function sumPackageServicePrices(packages = []) {
  return normalizePackageServices(packages).reduce((sum, service) => sum + Number(service.price || 0), 0)
}

export function mapPackageServicesForDisplay(packages = []) {
  return normalizePackageServices(packages).map((service, index) => {
    const detailParts = []

    if (service.details) detailParts.push(service.details)
    if (service.price > 0) detailParts.push(`$${service.price.toLocaleString()}`)

    return {
      name: service.name || `Service ${index + 1}`,
      detail: detailParts.join(' | ') || 'Included in this package.',
      price: Number(service.price || 0),
    }
  })
}

export function resolveEventPrice({ packages = [], serviceMode = 'overall', fallbackPrice = 0 } = {}) {
  const packageTotal = sumPackageServicePrices(packages)

  if (serviceMode === 'package' && packageTotal > 0) {
    return packageTotal
  }

  return Number(fallbackPrice || 0)
}

export function formatEventPriceLabel(price, serviceMode = 'overall') {
  const formattedPrice = `$${Number(price || 0).toLocaleString()}`

  return serviceMode === 'package' ? formattedPrice : `From ${formattedPrice}`
}
