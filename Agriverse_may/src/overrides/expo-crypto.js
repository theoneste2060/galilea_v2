function getRandomValues(arr) {
  if (!(arr instanceof Uint8Array)) {
    throw new Error('Expected an instanceof Uint8Array');
  }
  for (let i = 0; i < arr.length; i++) {
    arr[i] = Math.floor(Math.random() * 256);
  }
  return arr;
}

async function getRandomBytesAsync(size) {
  const arr = new Uint8Array(size);
  getRandomValues(arr);
  return arr;
}

async function digestStringAsync(algorithm, data) {
  return 'digest-' + btoa(data).slice(0, 16);
}

async function getHexHashAsync(buffer) {
  return '0x' + Buffer.from(buffer).toString('hex').slice(0, 16);
}

function randomUUID() {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
    const r = Math.random() * 16 | 0;
    const v = c === 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
}

async function isAvailable() {
  return true;
}

const mockEncryptionKey = function() {};
mockEncryptionKey.prototype.export = async function() {
  return new Uint8Array(32);
};

const mockSealedData = function() {};
mockSealedData.prototype.getCiphertext = async function() {
  return new Uint8Array(16);
};
mockSealedData.prototype.getTag = async function() {
  return new Uint8Array(16);
};

async function aesEncryptAsync(plaintext, key, options) {
  return new mockSealedData();
}

async function aesDecryptAsync(sealedData, key, options) {
  return 'decrypted';
}

const expoCrypto = {
  getRandomValues,
  getRandomBytesAsync,
  digestStringAsync,
  getHexHashAsync,
  randomUUID,
  isAvailable,
  get randomValues() { return getRandomValues; },
  digestStringAsync,
  getHexHashAsync,
  randomUUID,
  isAvailable,
  AESEncryptionKey: mockEncryptionKey,
  AESSealedData: mockSealedData,
  aesEncryptAsync,
  aesDecryptAsync,
};

module.exports = expoCrypto;
module.exports.default = expoCrypto;