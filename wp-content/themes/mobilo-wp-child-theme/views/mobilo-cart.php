<?php

/**
 * Template Name: Mobilo Cart
 * Used on mobilo_cart shortcode
 */
?>

<!-- add tailwind css -->
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
<script src="https://cdn.tailwindcss.com"></script>
<main class="grid grid-cols-1 lg:grid-cols-3 gap-8">
    <div class="lg:col-span-2">
        <h2 class="text-3xl font-bold text-gray-800 mb-8">Choose your card</h2>
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col md:flex-row items-center gap-6">
                <img alt="Custom Designed Card" class="w-48 h-auto"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuBVCez48N9mkTozhkxGe6ryL3GLEDGFOIhRoGUlEpGuDVFOayUqD4ouaogtv7gC-lvUa2ltjS1aJUIcxrfDD2p66jIaNoYbSwp8uZbAKaJRNKY7v3S8S4UliFxzP_8lRVrdd1eiWAkPLxwNW6QNZFSQ69rBn0RHZAOAC2qBzwl7Zq9nG-FWdIMqaRGNlp5avwNXmwM5sOQnM4cZHXVRIyMfhvC80RhS8e7d5PsT1jOnp4Gbiraev_cvtFQ7aAnA4_kYn9151rjNsOB5" />
                <div class="flex-grow">
                    <h3 class="text-xl font-bold text-gray-800">Custom Designed Card</h3>
                    <p class="text-gray-500 mb-4">+ Free Digital Card</p>
                    <ul class="space-y-2 text-gray-600 mb-4">
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>NFC chip</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Works with Apple and
                            Android</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Unlimited uses</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Ships within 48 hours
                        </li>
                    </ul>
                    <div class="flex items-center space-x-4 mb-4">
                        <span class="font-medium text-gray-700">Card material:</span>
                        <div class="flex space-x-2">
                            <button class="px-4 py-1 rounded-full text-sm border">Classic</button>
                            <button class="px-4 py-1 rounded-full text-sm border">Wood</button>
                            <button class="px-4 py-1 rounded-full text-sm bg-gray-800 text-white">Metal</button>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <img alt="Silver card color" class="w-8 h-8 rounded-full border-2 border-blue-500"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAq3SUebTVPInWRj3RmR5aLKyyVmY9GBNhXjRC63qtmBayiWZRxRQ_hv70vuZrG6vW5xbhzjSQ03NZjd8nvWvlgIGn0VhMf4pExnCBOhtqySig0ThdWitWw3wdeE7OutGSYynM5d6N5RrvFwCb_D4t-uuz7SY9UTieZc3g2wPFzDnwwsIhbMYtFACpgqEE5IyKu08FuxSgaROnp0qpTYJ2YT9dBgi46lz9t9qRxUh4jrGb8EuPKMs_i1Hi23GUaKOBWPC5WMRd3GPO7" />
                        <img alt="Gray card color" class="w-8 h-8 rounded-full"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuCsBZs6QLDjQeYxMHqFZfvqYrTUzRa3sf4cHYOPfeIho-HJ0Vn6w6Ck_F79v9oYLdMY0XPayFdKyK-YgHjAU7ml4FrtDQZo4dhIH68otv0acDflTvv4XSMIiCThXQRKJ3H_G4sjuB7Du9ENehb2CmQVBkpHyPcFOtL_LwOK4s26T3imuosLNNvjOlDuys4u1DPpGWyibUji8Ux4O1itdjBh_75Y56jOjpo6hMfW_qCYb69G91ulNUgdtKJGSJuSCcY4lRwbO_Deyqow" />
                        <img alt="Black card color" class="w-8 h-8 rounded-full"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAfzyhRp74Pddv6KQNaj_YeaLMsHadsD9lW-wC-LUEM9bA7t_v67HFMOqDscTzv2g8J6xRfQoET0YS-y_Ex815_Ry9OPNUTweW8Z6X1E4dLQF3BKk2_r6L0YkvKYeG-h5S9Br5CC2g2BiwcSAKiW2Mf-1xwUxPrQNVs7PKV46eOXP97v0d3EgKOykC4uoWbK1piZUG8YgrKPHcnCHV8YAxySNROdsf63JECldicmXWEmzeYq7FoPVxGaPW3PgPVJf_Cy2zAP2ddjK7n" />
                    </div>
                </div>
                <div class="flex flex-col items-end self-stretch justify-between w-full md:w-auto">
                    <div class="text-right">
                        <p class="text-gray-400 line-through text-sm">$139.00</p>
                        <p class="text-xl font-bold text-gray-800">$69.00</p>
                    </div>
                    <button
                        class="w-full md:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Add</button>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col md:flex-row items-center gap-6">
                <img alt="Mobilo Branded Card" class="w-48 h-auto"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCI8amgMWOKQyjbtwdjLnLVk-dXsfk6FCSITEovYXjbq0atZU_hu6TEwVQmw4b-pW9BBhjVjrwVkDV-5MokYh7qC1_3vZOFs0zwc0hyDZ3ro5D05k0SsRjeSD5O_rlg-ygmAWDG8pQkSQ01Y38kgrohUHfj3wtWgW3vulzOMAwmRJSwjcMXfZSQLTbBiwhWEv0Jkt1HGoC6NoaYYsB4o7-2FcaJ1CyGnHmVXwtmJvPiq0vXNLmqhuK14XT3dhhU_O4aOlVwXZqnzy-4" />
                <div class="flex-grow">
                    <h3 class="text-xl font-bold text-gray-800">Mobilo Branded Card</h3>
                    <p class="text-gray-500 mb-4">+ Free Digital Card</p>
                    <ul class="space-y-2 text-gray-600 mb-4">
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Works with Apple and
                            Android</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>NFC/RFID Enabled</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>QR Code for older phones
                        </li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Ships within 48hrs</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Ships the same day</li>
                    </ul>
                </div>
                <div class="flex flex-col items-end self-stretch justify-between w-full md:w-auto">
                    <div class="text-right">
                        <p class="text-gray-400 line-through text-sm">$7.99</p>
                        <p class="text-xl font-bold text-gray-800">$4.99</p>
                    </div>
                    <button
                        class="w-full md:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Add</button>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm flex flex-col md:flex-row items-center gap-6">
                <img alt="Digital Card" class="w-48 h-auto object-contain"
                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCUe6pPIgzOsscL8221O704Nhs6gnk2cfUMACKnvhQB4_IL1-rS4MT4PfdYltRkwNHYuNDTEck838vPzLFC-5tXW81N977fwST8YskAeF-z8tU1DopVZmtRi1ppmxgmmKLNQ-ckq0pYx5It2UwwVUSsvbrz7eD0oyjGZEYtN11RvEnONwZv9OcckGMrMN8LnbuUR97uKLj1_EiMy2tE1fpmVpfpqV3bmtmcNZO0FZdgEqtAVQsYMHBh3JiTsRh-Sn18Zp-V9Zu1luJ5" />
                <div class="flex-grow">
                    <h3 class="text-xl font-bold text-gray-800">Digital Card</h3>
                    <ul class="space-y-2 text-gray-600 my-4">
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Works with Apple &amp;
                            Android</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Unlimited uses</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Access instantly</li>
                    </ul>
                </div>
                <div class="flex flex-col items-end self-stretch justify-between w-full md:w-auto">
                    <p class="text-xl font-bold text-gray-800">Free</p>
                    <button
                        class="w-full md:w-auto bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">Add</button>
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex justify-center mb-4">
                        <img alt="NFC Key Fob" class="h-24 w-auto"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuDn2Uk2ihCp5GdaRz6j_WCoA28rVjHhQ2awi-t_f89gIrb3cpyIildEf8guI3SfnF-bkawCGw6Cw-KGjIzqRdFpxJNoJoB9MccNBK1NdzK9CzB782hyzFgfoD2ih9H7EJ4kNAxFv2EU8n4n5nNMN4_OEqqfRJpXgz1U-utvz2P4R1whb8CKzom3dKheS68jvT9z2MhLkHBlxBBMlr1E7TIk2-YVmUOEO20CQQ4ZIqSxl75kRcZXDinpeDHd8Jig4MIo8MKcxmYG57LA" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 text-center">NFC Key Fob</h3>
                    <p class="text-gray-600 text-center my-2">Attach to your keys and never worry about leaving
                        your card at home.</p>
                    <div class="flex justify-between items-center mt-4">
                        <p class="text-lg font-bold text-gray-800">1x $2.50</p>
                        <button
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Add
                            for all members</button>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm">
                    <div class="flex justify-center mb-4">
                        <img alt="NFC Smart Button" class="h-24 w-auto"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuD-1V64rbQoGaPwafe7EapEuShFucC3zceSbpOU3oocSLWO32B4VKvZgA_SX3VFuDkR71W1vZ9vohlOi2J8rXc_AcgoHn-SRRxCJOcCVLycClTytMPumc7YmRs266Il2qK0FXeuk6eezcfkp89GC6p1MEdfSwdnXSgxCuJZNvsieshdvwhJt5WzKjEgw-Uo4q9ZmJIjN23MAJP0hGyLR8HjYW3eCaeHrR_xvYRjeEQJ2vJo5a6fzkzJkS8QhNfwVXPHl8Oz2rGddvTS" />
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 text-center">NFC Smart Button</h3>
                    <p class="text-gray-600 text-center my-2">Stick to the back of your phone and always be
                        ready to connect.</p>
                    <div class="flex justify-between items-center mt-4">
                        <p class="text-lg font-bold text-gray-800">1x $2.50</p>
                        <button
                            class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Add
                            for all members</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="lg:col-span-1">
        <div class="bg-white p-6 rounded-lg shadow-sm top-8">
            <div class="border-b pb-4 mb-4">
                <div class="flex justify-between items-center mb-4">
                    <div class="flex items-center gap-4">
                        <div class="p-2 bg-gray-100 rounded-lg">
                            <span class="material-icons text-gray-600">business_center</span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">Products</p>
                            <p class="text-sm text-gray-500">Cards &amp; Accessories</p>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="font-medium text-gray-700">Custom Designed Card</p>
                        <p class="text-sm text-gray-500">Metal * Silver</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <p class="font-bold text-gray-800">$69.00</p>
                        <div class="flex items-center border rounded-md">
                            <button class="px-2 py-1 text-gray-500">-</button>
                            <span class="px-2 py-1">1</span>
                            <button class="px-2 py-1 text-gray-500">+</button>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <span class="material-icons text-gray-600">person</span>
                        <div>
                            <p class="font-medium text-gray-700">Pro</p>
                            <p class="text-sm text-gray-500">Free up to 5 members</p>
                        </div>
                    </div>
                    <p class="font-bold text-gray-800">$0.00</p>
                </div>
            </div>
            <div class="flex justify-between items-center mb-4">
                <p class="font-bold text-lg text-gray-800">Order total:</p>
                <p class="font-bold text-lg text-gray-800">$69.00</p>
            </div>
            <button
                class="w-full bg-blue-600 text-white py-3 rounded-lg font-bold hover:bg-blue-700 mb-4">Checkout</button>
            <p class="text-center text-sm text-gray-500 mb-4">$69.00 one-time</p>
            <div class="bg-gray-100 p-4 rounded-lg text-gray-600 text-sm space-y-2">
                <div class="flex items-start gap-2">
                    <span class="material-icons mt-1">local_shipping</span>
                    <p>Shipping will be calculated at checkout</p>
                </div>
                <div class="flex items-start gap-2">
                    <span class="material-icons mt-1">palette</span>
                    <p>Custom designs will be created after payment</p>
                </div>
            </div>
        </div>
        <div class="bg-white p-6 rounded-lg shadow-sm mt-8">
            <p class="text-sm text-gray-500 mb-2">Chosen plan</p>
            <p class="font-bold text-gray-800 mb-2">PRO</p>
            <h2 class="text-4xl font-bold text-gray-800 mb-4">Free</h2>
            <button
                class="w-full border border-gray-300 text-gray-700 py-2 rounded-lg font-medium hover:bg-gray-100 mb-4">All
                Mobilo features</button>
            <div class="text-center text-sm text-gray-500 mb-6 flex items-center justify-center gap-2">
                <span class="material-icons">group</span>
                <span>6-250 members</span>
            </div>
            <div class="space-y-4 text-sm">
                <div>
                    <h4 class="font-bold text-gray-800 mb-2">Contact Sharing</h4>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Unlimited taps/card
                            shares</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Personalized business
                            card templates</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Apple/Google QR Code
                            Widget</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Digital Wallet Card</li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 mb-2">Lead management</h4>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Unlimited lead capture
                        </li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>4,000+ CRM integrations
                            with Zapier</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Native integrations</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Mobilo AI: Lead Scoring
                            &amp; more</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Business Card Scanner
                        </li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 mb-2">Team management and reporting</h4>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Brand governance: control
                            employee profiles</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Create Departments,
                            Groups, Office Locations</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Team insights &amp;
                            analytics</li>
                        <li class="flex items-center"><span
                                class="material-icons text-green-500 mr-2">check</span>Malware link checker</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>