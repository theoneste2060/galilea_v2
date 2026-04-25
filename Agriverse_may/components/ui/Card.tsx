import React from 'react';
import { View, ViewStyle } from 'react-native';

interface CardProps {
  children: React.ReactNode;
  style?: ViewStyle;
}

export function Card({ children, style }: CardProps) {
  return (
    <View
      style={[
        cardStyle,
        style,
      ]}
    >
      {children}
    </View>
  );
}

const cardStyle: ViewStyle = {
  backgroundColor: '#fff',
  borderRadius: 16,
  borderWidth: 1,
  borderColor: '#E5E7EB',
  padding: 16,
};