import React from 'react';
import {
    SearchOutlined, DatabaseOutlined, ShoppingCartOutlined, ReconciliationOutlined, BorderlessTableOutlined, DownloadOutlined,
    GoldOutlined, FileImageOutlined, FileTextOutlined, BarcodeOutlined, LineChartOutlined, DollarCircleOutlined, ContainerOutlined,
    FileMarkdownOutlined, ClusterOutlined, FileSearchOutlined, FileSyncOutlined, OrderedListOutlined
} from '@ant-design/icons';

const Element = (props: any) => {

    const { item } = props;

    const printElement = (id: number) => {
        switch (item.id) {
            // Header/Footer Elements
            case 'search-input':
                return (<SearchOutlined />);
            case 'category-filter':
                return (<DatabaseOutlined />)
            case 'add-selected-to-cart-button':
                return (<ShoppingCartOutlined />);
            case 'cart-subtotal':
                return (<ReconciliationOutlined />);
            case 'product-count':
                return (<BorderlessTableOutlined />);
            case 'pagination':
                return (<DownloadOutlined />);
            case 'attribute-filter':
                return (<GoldOutlined />);
            case 'search-button':
                return (<FileSearchOutlined />);
            case 'clear-filters':
                return (<FileSyncOutlined />);
            // Table Elements
            case 'product-image':
                return (<FileImageOutlined />);
            case 'product-name':
                return (<FileTextOutlined />);
            case 'sku':
                return (<BarcodeOutlined />);
            case 'in-stock-amount':
                return (<LineChartOutlined />);
            case 'price':
                return (<DollarCircleOutlined />);
            case 'quantity-input':
                return (<DownloadOutlined />);
            case 'add-to-cart-button':
                return (<ShoppingCartOutlined />);
            case 'combo-variation-dropdown':
                return (<ContainerOutlined />);
            case 'standard-variation-dropdowns':
                return (<ContainerOutlined />);
            case 'product-meta':
                return (<FileMarkdownOutlined />);
            case 'global-attribute':
                return (<ClusterOutlined />);
            case 'short-description':
                return (<FileTextOutlined />);
            case 'add-to-cart-checkbox':
                return (<OrderedListOutlined />);
            // WooCommerce Widgets
            case 'cart-widget':
                return (<ReconciliationOutlined />);
            case 'filter-products-by-attribute':
                return (<GoldOutlined />);
            case 'filter-products-by-price':
                return (<GoldOutlined />);
        }
    }

    if (typeof item !== 'undefined' && typeof item.id !== 'undefined')
        return (
            <>
                {printElement(item.id)} {item.content}
            </>
        )
    else return (<></>)

}

export default Element;