import { View, Text, TextInput, TextInputProps, ViewStyle, TextStyle } from 'react-native';

interface InputProps extends TextInputProps {
  label?: string;
  error?: string;
}

export function Input({ label, error, style, ...props }: InputProps) {
  return (
    <View style={containerStyle}>
      {label && (
        <Text style={labelStyle}>{label}</Text>
      )}
      <TextInput
        style={[inputStyle, style, error ? errorInputStyle : null]}
        placeholderTextColor="#9CA3AF"
        {...props}
      />
      {error && <Text style={errorTextStyle}>{error}</Text>}
    </View>
  );
}

const containerStyle: ViewStyle = {
  gap: 8,
};

const labelStyle: TextStyle = {
  fontSize: 14,
  fontWeight: '500',
  color: '#4B5563',
};

const inputStyle: TextStyle = {
  height: 56,
  paddingHorizontal: 16,
  borderRadius: 16,
  borderWidth: 1,
  borderColor: '#E5E7EB',
  backgroundColor: '#fff',
  fontSize: 16,
  color: '#111827',
};

const errorInputStyle: TextStyle = {
  borderColor: '#EF4444',
};

const errorTextStyle: TextStyle = {
  fontSize: 14,
  color: '#EF4444',
};