import React from 'react';
import { View, StyleSheet, ActivityIndicator } from 'react-native';
import { Tabs, Stack, Slot } from 'expo-router';
import { ClerkProvider, useAuth } from '@clerk/clerk-expo';
import { ConvexProvider } from 'convex/react';
import { ConvexReactClient } from 'convex/react';
import * as SecureStore from 'expo-secure-store';

const CLERK_PUBLISHABLE_KEY = 'pk_test_bGVuaWVudC1saW9uZXNzLTg3LmNsZXJrLmFjY291bnRzLmRldiQ';
const CONVEX_URL = 'https://tremendous-chicken-58.convex.cloud';

const convex = new ConvexReactClient(CONVEX_URL);

function LoadingScreen() {
  return (
    <View style={styles.loading}>
      <ActivityIndicator size="large" color="#10B981" />
    </View>
  );
}

function AppTabs() {
  return (
    <Tabs screenOptions={{ headerShown: false, tabBarStyle: styles.tabBar }}>
      <Tabs.Screen name="index" options={{ title: 'Feed' }} />
      <Tabs.Screen name="profile" options={{ title: 'Profile' }} />
    </Tabs>
  );
}

function RootNavigator() {
  const { isLoaded, isSignedIn } = useAuth();

  if (!isLoaded) {
    return <LoadingScreen />;
  }

  if (isSignedIn) {
    return <AppTabs />;
  }

  return <Slot />;
}

const tokenCache = {
  getToken: async (key: string) => SecureStore.getItemAsync(key),
  saveToken: async (key: string, token: string) => SecureStore.setItemAsync(key, token),
  removeToken: async (key: string) => SecureStore.deleteItemAsync(key),
};

export default function RootLayout() {
  return (
    <ClerkProvider publishableKey={CLERK_PUBLISHABLE_KEY} tokenCache={tokenCache}>
      <ConvexProvider client={convex}>
        <RootNavigator />
      </ConvexProvider>
    </ClerkProvider>
  );
}

const styles = StyleSheet.create({
  loading: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#fff',
  },
  tabBar: {
    backgroundColor: '#fff',
    borderTopWidth: 1,
    borderTopColor: '#E5E7EB',
    height: 80,
    paddingBottom: 20,
  },
});