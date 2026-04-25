# Agriverse - Mobile App Specification

## 1. Project Overview
- **Project Name**: Agriverse
- **Type**: React Native mobile app (Expo)
- **Core Functionality**: Social feed with authentication, real-time posts
- **Target Users**: Agricultural community members

## 2. Tech Stack
- Expo SDK 52 (React Native)
- TypeScript
- Expo Router (file-based routing)
- Clerk (authentication)
- Convex (backend + real-time database)
- NativeWind (Tailwind CSS for React Native)

## 3. UI/UX Specification

### Color Palette
- Primary: #10B981 (emerald-500)
- Background: #FFFFFF
- Card Background: #FFFFFF
- Border: #E5E7EB (gray-200)
- Text Primary: #111827 (gray-900)
- Text Secondary: #6B7280 (gray-500)
- Text Muted: #9CA3AF (gray-400)
- Accent: #059669 (emerald-600)

### Typography
- Font: System default (San Francisco on iOS)
- Heading: 20px, font-weight 600
- Body: 16px, font-weight 400
- Caption: 14px, font-weight 400
- Small: 12px

### Spacing System
- Base unit: 4
- xs: 4px
- sm: 8px
- md: 16px
- lg: 24px
- xl: 32px

### Corner Radius
- rounded-xl: 12px
- rounded-2xl: 16px (main)
- Full: 9999

### Components
1. Button
   - Variants: primary, secondary, outline
   - States: default, pressed, disabled
   - Size: full-width, h-12

2. Input
   - Rounded corners (rounded-2xl)
   - Border: gray-200
   - Padding: p-4
   - Focus ring

3. Card
   - White background
   - Border: gray-200
   - Rounded-2xl
   - Shadow: subtle

4. Avatar
   - Size: 40px (post), 48px (profile)
   - Circle shape
   - Placeholder: initials

## 4. Screen Structure

### (auth) Group
- /sign-in (Route: /sign-in)
- /sign-up (Route: /sign-up)

### (tabs) Group
- / (Route: /) - Feed
- /profile (Route: /profile)

## 5. Functionality Specification

### Authentication
- Sign up with email/password via Clerk
- Sign in with email/password via Clerk
- Persistent session via Clerk
- Logout functionality
- Protected routes redirect to /sign-in

### Feed Page
- Real-time posts list via Convex
- FlatList with post cards
- Create post input + button
- Pull-to-refresh
- Empty state when no posts
- Loading state

### Profile Page
- User info display
- Logout button

### Data Schema

#### users
- _id: string
- clerkId: string
- username: string
- createdAt: number

#### posts
- _id: string
- userId: string
- content: string
- createdAt: number

### Functions
- createPost(content, userId) -> post
- getFeed() -> posts[]
- getUserPosts(userId) -> posts[]
- getUser(clerkId) -> user

## 6. File Structure
/app
  /(auth)
    sign-in.tsx
    sign-up.tsx
    _layout.tsx
  /(tabs)
    index.tsx
    profile.tsx
    _layout.tsx
  _layout.tsx
  (root)/_layout.tsx
  +html.tsx
/components
  ui/
    Button.tsx
    Input.tsx
    Card.tsx
    Avatar.tsx
/convex
  schema.ts
  functions.ts
/lib
  clerk.ts
  convex.tsx