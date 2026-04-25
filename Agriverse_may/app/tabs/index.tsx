import { useState, useEffect } from 'react';
import { View, FlatList, Text, StyleSheet, Alert, ActivityIndicator, RefreshControl, KeyboardAvoidingView, Platform, Pressable } from 'react-native';
import { useAuth, useUser } from '@clerk/clerk-expo';
import { useQuery, useMutation } from 'convex/react';
import { api } from '../../convex/_generated/api';
import { Card } from '../../components/ui/Card';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Avatar } from '../../components/ui/Avatar';
import { Ionicons } from '@expo/vector-icons';

interface Post {
  _id: string;
  _creationTime: number;
  userId: string;
  content: string;
  createdAt: number;
}

function formatRelativeTime(timestamp: number): string {
  const now = Date.now();
  const diff = now - timestamp;
  const minutes = Math.floor(diff / 60000);
  const hours = Math.floor(diff / 3600000);
  const days = Math.floor(diff / 86400000);

  if (minutes < 1) return 'Just now';
  if (minutes < 60) return `${minutes}m ago`;
  if (hours < 24) return `${hours}h ago`;
  return `${days}d ago`;
}

export default function FeedScreen() {
  const { userId } = useAuth();
  const { user } = useUser();
  const [postContent, setPostContent] = useState('');
  const [creating, setCreating] = useState(false);
  const [refreshing, setRefreshing] = useState(false);

  const posts = useQuery(api.functions.getFeed);
  const createPost = useMutation(api.functions.createPost);

  const handleCreatePost = async () => {
    if (!postContent.trim()) {
      Alert.alert('Error', 'Please enter some content');
      return;
    }

    if (!userId) return;

    setCreating(true);
    try {
      await createPost({ content: postContent, userId });
      setPostContent('');
    } catch (err: any) {
      Alert.alert('Error', err.message || 'Failed to create post');
    } finally {
      setCreating(false);
    }
  };

  const onRefresh = async () => {
    setRefreshing(true);
    setTimeout(() => setRefreshing(false), 1000);
  };

  const renderPost = ({ item }: { item: Post }) => (
    <Card style={styles.postCard}>
      <View style={styles.postHeader}>
        <Avatar name="User" size={40} />
        <View style={styles.postInfo}>
          <Text style={styles.username}>User</Text>
          <Text style={styles.timestamp}>{formatRelativeTime(item.createdAt)}</Text>
        </View>
        <Pressable style={styles.moreButton}>
          <Ionicons name="ellipsis-horizontal" size={18} color="#9CA3AF" />
        </Pressable>
      </View>
      <Text style={styles.postContent}>{item.content}</Text>
      <View style={styles.postActions}>
        <Pressable style={styles.actionButton}>
          <Ionicons name="heart-outline" size={20} color="#6B7280" />
          <Text style={styles.actionText}>Like</Text>
        </Pressable>
        <Pressable style={styles.actionButton}>
          <Ionicons name="chatbubble-outline" size={20} color="#6B7280" />
          <Text style={styles.actionText}>Comment</Text>
        </Pressable>
        <Pressable style={styles.actionButton}>
          <Ionicons name="share-outline" size={20} color="#6B7280" />
          <Text style={styles.actionText}>Share</Text>
        </Pressable>
      </View>
    </Card>
  );

  if (posts === undefined) {
    return (
      <View style={styles.loading}>
        <ActivityIndicator size="large" color="#10B981" />
      </View>
    );
  }

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <View style={styles.header}>
        <Text style={styles.headerTitle}>Agriverse</Text>
      </View>

      <FlatList
        data={posts || []}
        keyExtractor={(item) => item._id}
        renderItem={renderPost}
        contentContainerStyle={styles.list}
        ListHeaderComponent={
          <Card style={styles.createCard}>
            <View style={styles.createHeader}>
              <Avatar name={user?.username || 'U'} size={40} />
              <Text style={styles.createTitle}>What's happening?</Text>
            </View>
            <Input
              placeholder="Share your thoughts with the community..."
              value={postContent}
              onChangeText={setPostContent}
              multiline
              numberOfLines={4}
            />
            <Button
              title="Post"
              onPress={handleCreatePost}
              loading={creating}
              disabled={!postContent.trim()}
              style={styles.postButton}
            />
          </Card>
        }
        ListEmptyComponent={
          <View style={styles.empty}>
            <Ionicons name="newspaper-outline" size={48} color="#9CA3AF" />
            <Text style={styles.emptyTitle}>No posts yet</Text>
            <Text style={styles.emptySubtitle}>Be the first to share something!</Text>
          </View>
        }
        refreshControl={
          <RefreshControl
            refreshing={refreshing}
            onRefresh={onRefresh}
            tintColor="#10B981"
          />
        }
      />
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#F9FAFB',
  },
  loading: {
    flex: 1,
    justifyContent: 'center',
    alignItems: 'center',
    backgroundColor: '#fff',
  },
  header: {
    paddingTop: 60,
    paddingBottom: 16,
    paddingHorizontal: 16,
    backgroundColor: '#fff',
    borderBottomWidth: 1,
    borderBottomColor: '#E5E7EB',
  },
  headerTitle: {
    fontSize: 24,
    fontWeight: '600',
    color: '#111827',
  },
  list: {
    padding: 16,
    gap: 12,
  },
  createCard: {
    marginBottom: 8,
  },
  createHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  createTitle: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
    marginLeft: 12,
  },
  postButton: {
    marginTop: 12,
  },
  postCard: {
    marginBottom: 4,
  },
  postHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    marginBottom: 12,
  },
  postInfo: {
    flex: 1,
    marginLeft: 12,
  },
  username: {
    fontSize: 16,
    fontWeight: '600',
    color: '#111827',
  },
  timestamp: {
    fontSize: 12,
    color: '#9CA3AF',
    marginTop: 2,
  },
  moreButton: {
    padding: 4,
  },
  postContent: {
    fontSize: 16,
    color: '#111827',
    lineHeight: 24,
  },
  postActions: {
    flexDirection: 'row',
    marginTop: 16,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: '#F3F4F6',
  },
  actionButton: {
    flexDirection: 'row',
    alignItems: 'center',
    marginRight: 24,
  },
  actionText: {
    fontSize: 14,
    color: '#6B7280',
    marginLeft: 6,
  },
  empty: {
    alignItems: 'center',
    paddingTop: 60,
  },
  emptyTitle: {
    fontSize: 18,
    fontWeight: '600',
    color: '#111827',
    marginTop: 16,
    marginBottom: 8,
  },
  emptySubtitle: {
    fontSize: 14,
    color: '#6B7280',
  },
});