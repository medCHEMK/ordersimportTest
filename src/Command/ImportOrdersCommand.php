<?php 

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\Order;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\MarketPlace;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;


class ImportOrdersCommand extends Command
{

    protected static $defaultName = 'app:import-orders';

    private $entityManager;

    protected function configure(): void
    {
        $this->setHelp('This command imports orders');   
    }

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $output->writeln([
            'Order Import',
            '============',
            '',
        ]);
        $countOrder = $this->importOrder();
        $output->writeln([
            $countOrder,
            'Orders Imported',
            '============',
            '',
        ]);
        return Command::SUCCESS;

    }

    private function importOrder() : ?int
    {
        $countOrders = 0 ; 
        $xml = simplexml_load_file(__DIR__.'/../../data/data.xml', 'SimpleXMLElement');
 

        foreach ($xml->orders->order as $orderImported) {

            $order = $this->entityManager->getRepository(Order::class)->findOneBy(['uuid' => (string)$orderImported->id]);
            if($order) {
                continue;
            } else {
                $order = new Order();
            }

            // setting market place
            $marketPlace = $this->entityManager->getRepository(MarketPlace::class)->findOneBy(['name' => (string)$orderImported->marketplace]);
            if(!$marketPlace) {
                $marketPlace = new MarketPlace();
                $marketPlace->setName((string)$orderImported->marketplace);
                $this->entityManager->persist($marketPlace);
            }
            $order->setMarketPlace($marketPlace);
            //setting product
            $product = $this->entityManager->getRepository(Product::class)->findOneBy(['ref' => $orderImported->product->ref]);
            if(!$product){
                $product = new Product();
                $product->setRef($orderImported->product->ref);
                $product->setLabel($orderImported->product->label);
                $this->entityManager->persist($product);
            }
            $order->setProduct($product);

            //setting user 
            $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $orderImported->user->email]);
            if(!$user){
                $user = new user();
                $user->setEmail($orderImported->user->email);
                $user->setFirstname($orderImported->user->firstnme);
                $user->setLastname($orderImported->user->lastname);
                $user->setZip((int)$orderImported->user->zip);
                $user->setStreet($orderImported->user->street);
                $user->setCity($orderImported->user->city);
                $this->entityManager->persist($user);
            }
            $order->setUser($user);

            $order->setPrice((float)$orderImported->product->price);

            $order->setQuantity((int)$orderImported->product->attributes['qte']);
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                    
            $order->setUuid((string)$orderImported->id);
            $order->setCurrency((string)$orderImported->product->price->attributes['money']);

            

            $createdAt = DateTime::createFromFormat('Y-m-d\TH:i:sT',(string)$orderImported->created_at);
            $order->setCreatedAt($createdAt);
            $this->entityManager->persist($order);
            $countOrders++;
            

         
        }
        $this->entityManager->flush();
        return $countOrders;
    }
}