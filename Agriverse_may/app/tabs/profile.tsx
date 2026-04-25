import { useState } from 'react';
import { View, Text, StyleSheet, Alert, Pressable } from 'react-native';
import { useAuth, useUser } from '@clerk/clerk-expo';
import { useRouter } from 'expo-router';
import { Avatar } from '../../components/ui/Avatar';
import { Button } from '../../components/ui/Button';
import { Card } from '../../components/ui/Card';
import { Ionicons } from '@expo/vector-icons';

export default function ProfileScreen() {
  const { signOut, userId } = useAuth();
  const { user } = useUser();
  const router = useRouter();

  const handleSignOut = async () => {
    try {
      await signOut();
    } catch (err: any) {
      Alert.alert('Error', err.message || 'Failed to sign out');
    }
  };

  const username = user?.username || user?.emailAddresses[0]?.emailAddress.split('@')[0] || 'User';

  const MenuItem = ({ icon, title, subtitle, onPress }: { icon: string; title: string; subtitle?: string; onPress?: () => void }) => (
    <Pressable style={styles.menuItem} onPress={onPress}>
      <View style={styles.menuIcon}>
        <Ionicons name={icon as any} size={22} color="#10B981" />
      </View>
      <View style={styles.menuText}>
        <Text style={styles.menuTitle}>{title}</Text>
        {subtitle && <Text style={styles.menuSubtitle}>{subtitle}</Text>}
      </View>
      <Ionicons name="chevron-forward" size={20} color="#9CA3AF" />
    </Pressable>
  );

  return (
    <View style={styles.container}>
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Profile</Text>
      </View>

      <View style={styles.content}>
        <Card style={styles.profileCard}>
          <View style={styles.avatarContainer}>
            <Avatar name={username} size={80} />
          </View>
          <Text style={styles.username}>{username}</Text>
          <Text style={styles.email}>
            {user?.emailAddresses[0]?.emailAddress || 'No email'}
          </Text>
        </Card>

        <Text style={styles.sectionTitle}>Account</Text>
        <Card>
          <MenuItem icon="person-outline" title="Edit Profile" onPress={() => Alert.alert('Coming Soon', 'Profile editing will be available soon!')} />
          <MenuItem icon="notifications-outline" title="Notifications" subtitle="Manage your notifications" onPress={() => Alert.alert('Coming Soon', 'Notification settings will be available soon!')} />
          <MenuItem icon="lock-closed-outline" title="Privacy & Security" onPress={() => Alert.alert('Coming Soon', 'Privacy settings will be available soon!')} />
        </Card>

        <Text style={styles.sectionTitle}>Support</Text>
        <Card>
          <MenuItem icon="help-circle-outline" title="Help Center" onPress={() => Alert.alert('Coming Soon', 'Help center will be available soon!')} />
          <MenuItem icon="document-text-outline" title="Terms of Service" onPress={() => {}} />
          <MenuItem icon="shield-checkmark-outline" title="Privacy Policy" onPress={() => {}} />
        </Card>

        <View style={styles.actions}>
          <Button title="Sign Out" onPress={handleSignOut} variant="outline" />
        </View>
      </View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  header: {
    paddingTop: 60,
    paddingBottom: 16,
    paddingHorizontal: 16,
    backgroundColor: '#fff',
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: '600',
    color: '#111827',
  },
  content: {
    flex: 1,
    padding: 16,
  },
  profileCard: {
    alignItems: 'center',
    paddingVertical: 32,
    marginBottom: 24,
  },
  avatarContainer: {
    marginBottom: 16,
  },
  username: {
    fontSize: 20,
    fontWeight: '600',
    color: '#111827',
    marginBottom: 4,
  },
  email: {
    fontSize: 14,
    color: '#6B7280',
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '600',
    color: '#6B7280',
    marginBottom: 8,
    marginTop: 8,
    marginLeft: 4,
  },
  menuItem: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
  },
  menuIcon: {
    width: 36,
    height: 36,
    borderRadius: 8,
    backgroundColor: '#ECFDF5',
    alignItems: 'center',
    justifyContent: 'center',
    marginRight: 12,
  },
  menuText: {
    flex: 1,
  },
  menuTitle: {
    fontSize: 16,
    color: '#111827',
  },
  menuSubtitle: {
    fontSize: 13,
    color: '#6B7280',
    marginTop: 2,
  },
  actions: {
    marginTop: 24,
  },
});