import { useState } from 'react';
import { View, Text, StyleSheet, Alert, Pressable, KeyboardAvoidingView, Platform } from 'react-native';
import { useSignIn } from '@clerk/clerk-expo';
import { useRouter } from 'expo-router';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { Ionicons } from '@expo/vector-icons';

export default function SignInScreen() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [loading, setLoading] = useState(false);
  const { signIn, isLoaded } = useSignIn();
  const router = useRouter();

  const handleSignIn = async () => {
    if (!email || !password) {
      Alert.alert('Error', 'Please fill in all fields');
      return;
    }

    if (!isLoaded) return;

    setLoading(true);
    try {
      const result = await signIn.create({
        identifier: email,
        password,
      });

      if (result.status === 'complete') {
        router.replace('/');
      }
    } catch (err: any) {
      Alert.alert('Error', err.message || 'Failed to sign in');
    } finally {
      setLoading(false);
    }
  };

  const handleSSOSignIn = async (provider: string) => {
    if (!isLoaded) return;
    Alert.alert('Coming Soon', `Sign in with ${provider} will be available soon!`);
  };

  return (
    <KeyboardAvoidingView
      style={styles.container}
      behavior={Platform.OS === 'ios' ? 'padding' : 'height'}
    >
      <View style={styles.content}>
        <Text style={styles.title}>Welcome Back</Text>
        <Text style={styles.subtitle}>Sign in to continue</Text>

        <Pressable
          style={[styles.ssoButton, { backgroundColor: '#DB4437', marginBottom: 16 }]}
          onPress={() => handleSSOSignIn('Google')}
        >
          <Ionicons name="logo-google" size={20} color="#fff" style={styles.icon} />
          <Text style={styles.ssoButtonText}>Continue with Google</Text>
        </Pressable>
        
        <Pressable
          style={[styles.ssoButton, { backgroundColor: '#000000' }]}
          onPress={() => handleSSOSignIn('Apple')}
        >
          <Ionicons name="logo-apple" size={20} color="#fff" style={styles.icon} />
          <Text style={styles.ssoButtonText}>Continue with Apple</Text>
        </Pressable>

        <View style={styles.divider}>
          <View style={styles.dividerLine} />
          <Text style={styles.dividerText}>or</Text>
          <View style={styles.dividerLine} />
        </View>

        <View style={styles.form}>
          <View style={styles.inputContainer}>
            <Ionicons name="mail-outline" size={20} color="#6B7280" style={styles.inputIcon} />
            <Input
              placeholder="Email"
              value={email}
              onChangeText={setEmail}
              autoCapitalize="none"
              keyboardType="email-address"
              style={styles.input}
            />
          </View>
          <View style={styles.inputContainer}>
            <Ionicons name="lock-closed-outline" size={20} color="#6B7280" style={styles.inputIcon} />
            <Input
              placeholder="Password"
              value={password}
              onChangeText={setPassword}
              secureTextEntry
              style={styles.input}
            />
          </View>
          
          <Pressable style={styles.forgotPassword} onPress={() => Alert.alert('Coming Soon', 'Password reset will be available soon!')}>
            <Text style={styles.forgotPasswordText}>Forgot Password?</Text>
          </Pressable>
          
          <Button title="Sign In" onPress={handleSignIn} loading={loading} />
        </View>

        <View style={styles.footer}>
          <Text style={styles.footerText}>Don't have an account? </Text>
          <Pressable onPress={() => router.push('/sign-up')}>
            <Text style={styles.link}>Sign Up</Text>
          </Pressable>
        </View>
      </View>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: '#fff',
    justifyContent: 'center',
    padding: 24,
  },
  content: {
    paddingHorizontal: 16,
  },
  title: {
    fontSize: 28,
    fontWeight: '700',
    color: '#111827',
    textAlign: 'center',
  },
  subtitle: {
    fontSize: 16,
    color: '#6B7280',
    textAlign: 'center',
    marginTop: 4,
    marginBottom: 32,
  },
  ssoButtons: {
    gap: 16,
    marginBottom: 24,
  },
  ssoButton: {
    height: 52,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
  },
  icon: {
    marginRight: 10,
  },
  ssoButtonText: {
    color: '#fff',
    fontSize: 16,
    fontWeight: '600',
  },
  divider: {
    flexDirection: 'row',
    alignItems: 'center',
    marginVertical: 24,
  },
  dividerLine: {
    flex: 1,
    height: 1,
    backgroundColor: '#E5E7EB',
  },
  dividerText: {
    color: '#9CA3AF',
    marginHorizontal: 16,
    fontSize: 14,
  },
  form: {
    gap: 16,
  },
  inputContainer: {
    position: 'relative',
    height: 56,
  },
  inputIcon: {
    position: 'absolute',
    left: 14,
    top: 18,
    zIndex: 1,
  },
  input: {
    height: 56,
    paddingLeft: 54,
  },
  forgotPassword: {
    alignSelf: 'flex-end',
    marginTop: -8,
    marginBottom: 8,
  },
  forgotPasswordText: {
    fontSize: 14,
    color: '#10B981',
    fontWeight: '500',
  },
  footer: {
    flexDirection: 'row',
    justifyContent: 'center',
    marginTop: 32,
  },
  footerText: {
    fontSize: 14,
    color: '#6B7280',
  },
  link: {
    fontSize: 14,
    color: '#10B981',
    fontWeight: '600',
  },
});