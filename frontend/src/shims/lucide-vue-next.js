import { h } from 'vue'

const IconStub = {
  name: 'IconStub',
  inheritAttrs: false,
  setup(_props, { attrs }) {
    return () =>
      h(
        'span',
        {
          ...attrs,
          class: attrs.class,
          'aria-hidden': 'true',
          style: attrs.style,
        },
        '•',
      )
  },
}

export const Plus = IconStub
export const Bell = IconStub
export const TrendingUp = IconStub
export const CheckCircle2 = IconStub
export const Clock = IconStub
export const School = IconStub
export const Utensils = IconStub
export const Headphones = IconStub
export const ArrowUpRight = IconStub
export const Sparkles = IconStub
export const ChevronDown = IconStub
export const Search = IconStub
export const Edit2 = IconStub
export const Trash2 = IconStub
export const MoreVertical = IconStub
export const Package = IconStub
export const CalendarCheck = IconStub
export const Users = IconStub
export const Wallet = IconStub
export const Ticket = IconStub
export const Star = IconStub
export const ChefHat = IconStub
export const Cake = IconStub
export const Calendar = IconStub
export const MessageSquare = IconStub
export const Send = IconStub
export const PlusCircle = IconStub
export const Image = IconStub
export const CheckCheck = IconStub
export const User = IconStub
export const GraduationCap = IconStub
export const CalendarDays = IconStub
export const CreditCard = IconStub
export const XCircle = IconStub
export const FileText = IconStub
