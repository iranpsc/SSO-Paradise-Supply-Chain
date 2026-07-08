import EthereumProvider from "@walletconnect/ethereum-provider";

window.connectWalletConnect = async function () {
    const provider = await EthereumProvider.init({
        projectId: "159214f888fe7975147c3ce84bf0e31d",
        chains: [1],
        showQrModal: true,
    });

    await provider.connect();

    const address = provider.accounts?.[0];

    if (!address) {
        throw new Error("No wallet connected.");
    }

    // دریافت nonce از لاراول
    const nonceResponse = await fetch(`/web3/nonce?address=${address}`, {
        credentials: "same-origin",
    });

    if (!nonceResponse.ok) {
        throw new Error("Failed to fetch nonce.");
    }

    const nonceData = await nonceResponse.json();

    // تبدیل nonce به Hex (دقیقاً مثل MetaMask)
    const msgHex =
        "0x" +
        Array.from(new TextEncoder().encode(nonceData.nonce))
            .map((byte) => byte.toString(16).padStart(2, "0"))
            .join("");

    // امضا با WalletConnect
    const signature = await provider.request({
        method: "personal_sign",
        params: [msgHex, address],
    });

    return {
        provider,
        address,
        signature,
    };
};