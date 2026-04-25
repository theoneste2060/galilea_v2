import { Pressable, Text, ActivityIndicator, ViewStyle, TextStyle } from 'react-native';

interface ButtonProps {
  title: string;
  onPress: () => void;
  variant?: 'primary' | 'secondary' | 'outline';
  loading?: boolean;
  disabled?: boolean;
  style?: ViewStyle;
}

export function Button({ 
  title, 
  onPress, 
  variant = 'primary', 
  loading = false, 
  disabled = false,
  style,
}: ButtonProps) {
  const variantStyles: Record<string, ViewStyle> = {
    primary: { backgroundColor: '#10B981' },
    secondary: { backgroundColor: '#F3F4F6' },
    outline: { backgroundColor: 'transparent', borderWidth: 1, borderColor: '#E5E7EB' },
  };
  const textStyles: Record<string, TextStyle> = {
    primary: { color: '#fff' },
    secondary: { color: '#111827' },
    outline: { color: '#111827' },
  };

  return (
    <Pressable
      style={[
        baseStyles,
        variantStyles[variant],
        (disabled || loading) && disabledStyles,
        style,
      ]}
      onPress={onPress}
      disabled={disabled || loading}
    >
      {loading ? (
        <ActivityIndicator color={variant === 'primary' ? '#fff' : '#111827'} />
      ) : (
        <Text style={[textStyles[variant], textBase]}>{title}</Text>
      )}
    </Pressable>
  );
}

const baseStyles: ViewStyle = {
  height: 48,
  borderRadius: 16,
  alignItems: 'center',
  justifyContent: 'center',
  flexDirection: 'row',
};

const disabledStyles: ViewStyle = {
  opacity: 0.5,
};

const textBase: TextStyle = {
  fontWeight: '500',
  fontSize: 16,
};